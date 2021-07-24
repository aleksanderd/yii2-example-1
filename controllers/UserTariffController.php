<?php

namespace app\controllers;

use app\models\Tariff;
use app\models\User;
use app\models\UserTariffSearch;
use flyiing\helpers\Html;
use Yii;
use app\models\UserTariff;
use yii\base\DynamicModel;
use yii\db\IntegrityException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;
use yii\web\ServerErrorHttpException;

/**
 * UserTariffController implements the CRUD actions for UserTariff model.
 */
class UserTariffController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index', 'view', 'select', 'add', 'pay', 'delete',
                            'deactivate', 'activate', 'activate-form',
                            'toggle-renew', 'finish',
                            'dev-active'
                        ],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete', 'admin'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user->isAdmin;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'finish' => ['post'],
                ],
            ],
        ];
    }

    public function actionAdmin()
    {
        $searchModel = new UserTariffSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDevActive($user_id = null)
    {
        if (!YII_ENV_DEV) {
            throw new ForbiddenHttpException(Yii::t('app', 'Not allowed in production mode.'));
        }
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($user->isAdmin && isset($user_id) && ($u = User::findOne($user_id))) {
            $user = $u;
        }
        if (!($activeCurrent = Tariff::userGetActive($user))) {
            FlashHelper::setFlash('error', Yii::t('app', 'Active some tariff first.'));
            return $this->redirect(['index', 'user_id' => $user->id]);
        }
        $props = ['timeShift', 'minutes', 'messages', 'queries', 'space'];
        $model = new DynamicModel($props);
        $model->addRule($props, 'integer');
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            $msg = '';
            foreach ($props as $p) {
                if ($model->{$p} < 1) {
                    continue;
                }
                if ($p == 'timeShift') {
                    $activeCurrent->started_at -= $model->timeShift * 86400;
                    $msg .= Yii::t('app', 'Start time shifted by: {days} days.', ['days' => $model->timeShift]);
                } else if ($p == 'minutes') {
                    $activeCurrent->seconds_used += $model->{$p} * 60;
                    $msg .= Yii::t('app', 'Resource "{p}" decreased by: {n} minutes.', ['p' => $p, 'n' => $model->{$p}]);
                } else {
                    $activeCurrent->{$p . '_used'} += $model->{$p};
                    $msg .= Yii::t('app', 'Resource "{p}" decreased by: {n}', ['p' => $p, 'n' => $model->{$p}]);
                }
                $msg .= '<br>';
            }
            if ($activeCurrent->save()) {
                FlashHelper::setFlash('success', Yii::t('app', 'Active tariff data changed') .':<br>'. $msg);
                return $this->redirect(['index', 'user_id' => $user->id]);
            }
        }
        return $this->render('dev-active', compact('model', 'user'));
    }

    public function actionSelect($user_id = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $tariffs = Tariff::find();
        if (!$user->isAdmin) {
            $tariffs->andWhere(['>=', 'status', Tariff::STATUS_PUBLIC]);
        }
        if ($user->isAdmin && isset($user_id) && ($u = User::findOne($user_id))) {
            $user = $u;
        }
        $tariffs->orderBy(['title' => SORT_DESC]);
        //$sql = $tariffs->createCommand()->rawSql;
        $tariffs = $tariffs->all();
        return $this->render('select', compact('tariffs', 'user'));
    }

    protected function clearRenewFlag($user_id, $id)
    {
        /** @var UserTariff[] $active */
        $active = UserTariff::find()->where(['AND',
            ['user_id' => $user_id, 'renew' => 1],
            ['NOT', ['OR', ['id' => $id], ['status' => UserTariff::STATUS_FINISHED]]]
        ])->all();
        $result = 0;
        foreach ($active as $a) {
            $a->renew = 0;
            $a->save();
            FlashHelper::setFlash('info', Yii::t('app', 'Auto renew for tariff "{tariff}" hardly disabled.',
                ['tariff' => $a->title]));
            $result++;
        }
        return $result;
    }

    /**
     * Добавляет тариф в список пользовательских тарифов со статусом [[UserTariff::STATUS_DRAFT]] (не оплаченный).
     *
     * @param int|null $tariff_id
     * @param int|null $user_id
     * @param bool $pay
     * @return Response
     */
    public function actionAdd($tariff_id = null, $user_id = null, $pay = false)
    {
        if (!$tariff_id) {
            $this->redirect(['select', 'user_id' => $user_id]);
        }
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($user_id === null || !$user->isAdmin) {
            $user_id = $user->id;
        }
        $tariff = Tariff::find()->where(['id' => $tariff_id]);
        if (!$user->isAdmin) {
            $tariff->andWhere(['>=', 'status', Tariff::STATUS_PUBLIC]);
        }
        $tariff = $tariff->one();
        /** @var \app\models\Tariff|null $tariff */
        if (!$tariff) {
            FlashHelper::setFlash('error', Yii::t('app', 'Tariff not available.'));
            $this->redirect(['select', 'user_id' => $user_id]);
        }
        $tt = ['tariff' => $tariff->title];
        /** @var UserTariff $model */
        $model = UserTariff::find()
            ->where(['user_id' => $user_id, 'tariff_id' => $tariff->id])
            ->andWhere(['>=', 'status', 0])
            ->one();
        if ($model) {
            FlashHelper::setFlash('error', Yii::t('app', 'Tariff "{tariff}" is already in the use list.', $tt));
            return $this->redirect(['select', 'user_id' => $model->user_id]);
        }
        $model = new UserTariff([
            'user_id' => $user_id,
            'tariff_id' => $tariff->id,
            'status' => UserTariff::STATUS_DRAFT,
        ]);
        $model->applyTariff($tariff);
        if ($model->save()) {
            FlashHelper::setFlash('success',
                Yii::t('app', 'Tariff "{tariff}" successfully added.', $tt));
            if ($pay) {
                return $this->redirect(['pay', 'id' => $model->id]);
            } else {
                return $this->redirect(['index', 'user_id' => $model->user_id]);
            }
        } else {
            FlashHelper::flashModelErrors($model->getErrors());
            return $this->redirect(['select', 'user_id' => $model->user_id]);
        }
    }

    /**
     * Оплата тарифа.
     *
     * @param $id
     * @return string|Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionPay($id)
    {
        $model = $this->findModel($id);
        $tt = ['tariff' => $model->title];
        if ($model->isPaid) {
            throw new BadRequestHttpException(Yii::t('app', 'Tariff "{tariff}" is paid already.', $tt));
        }
        if ($model->isArchived) {
            throw new BadRequestHttpException(Yii::t('app', 'Tariff "{tariff}" is archived.', $tt));
        }
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($model->price > 0 && $model->user->balance < $model->price) {
            $ts = Html::icon(Yii::$app->formatter->currencyCode, '{i}') . '%.02f';
            FlashHelper::setFlash('error', Yii::t('app',
                'Insufficient funds to buy this tariff. You need at least {price}, while you have just {balance}. Please add some funds.',
                ['price' => sprintf($ts, $model->price), 'balance' => sprintf($ts, $model->user->balance)]
            ));
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }
        $post = Yii::$app->request->post();
        $model->renew = ($model->renewable && ArrayHelper::getValue($post, 'UserTariff.renew', false)) ? 1 : 0;
        if (ArrayHelper::getValue($post, 'confirm', $model->price == 0)) {
            if ($model->price > 0) {
                $transaction = $model->createTransaction();
                if ($transaction->save()) {
                    if ($model->renew) {
                        $this->clearRenewFlag($model->user_id, $model->id);
                    }
                    FlashHelper::setFlash('success', Yii::t('app', 'Tariff "{tariff}" successfully paid.', $tt));
                } else {
                    throw new ServerErrorHttpException(Yii::t('app', 'Transaction saving error.'));
                }
            }
            $model->status = UserTariff::STATUS_READY;
            if (!$model->save(false, ['renew', 'status'])) {
                throw new ServerErrorHttpException(Yii::t('app', 'Can not save tariff status.'));
            }
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }
        return $this->render('pay', compact('model'));
    }

    public function actionDeactivate($id)
    {
        $model = $this->findModel($id);
        if ($model->status != UserTariff::STATUS_ACTIVE) {
            throw new BadRequestHttpException(Yii::t('app', 'The tariff is not active.'));
        }
        if ($model->lifetime > 0) {
            throw new BadRequestHttpException(Yii::t('app', 'The tariff is time limited, so can not be deactivated.'));
        }
        $model->status = UserTariff::STATUS_READY;
        if ($model->save()) {
            FlashHelper::setFlash('success', Yii::t('app', 'Tariff "{tariff}" deactivated.',
                ['tariff' => $model->title]));
        } else {
            FlashHelper::flashModelErrors($model->getErrors());
        }
        return $this->redirect(['index', 'user_id' => $model->user_id]);
    }

    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        if ($model->status != UserTariff::STATUS_READY) {
            FlashHelper::setFlash('error', Yii::t('app', 'The tariff can not be activated.'));
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }
        if ($model->lifetime > 0) {
            $ltActive = UserTariff::find()->where(['AND',
                ['user_id' => $model->user_id, 'status' => UserTariff::STATUS_ACTIVE],
                ['>', 'lifetime', 0],
            ])->all();
            if (count($ltActive) > 0) {
                FlashHelper::setFlash('error', Yii::t('app', 'You have an active time limited tariff already.'));
                return $this->redirect(['index', 'user_id' => $model->user_id]);
            }
        }
        $model->status = UserTariff::STATUS_ACTIVE;
        if (!isset($model->started_at) || $model->started_at == 0) {
            $model->started_at = time();
        }
        if ($model->save()) {
            FlashHelper::setFlash('success', Yii::t('app', 'Tariff "{tariff}" activated.',
                ['tariff' => $model->title]));
        } else {
            FlashHelper::flashModelErrors($model->getErrors());
        }
        return $this->redirect(['index', 'user_id' => $model->user_id]);

    }

    public function actionActivateForm($id)
    {
        // TODO Нада проверять, дорабатывать.
        $model = $this->findModel($id);
        if ($model->status != UserTariff::STATUS_READY) {
            FlashHelper::setFlash('error', Yii::t('app', 'The tariff can not be activated.'));
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }
        $paramsNames = ['start', 'started_at', 'renew'];
        $paramsModel = new DynamicModel($paramsNames);
        $paramsModel->addRule($paramsNames, 'safe');

        $post = Yii::$app->request->post();
        if ($paramsModel->load($post)) {
            if ($paramsModel->start > 0) {
                $model->started_at = time();
            } else {
                $model->started_at = $paramsModel->started_at;
            }
            $model->status = UserTariff::STATUS_ACTIVE;
            $model->started_at = time();
            if ($model->save()) {
                FlashHelper::setFlash('success', Yii::t('app', 'Tariff "{tariff}" activated successfully.',
                    ['tariff' => $model->title]));
                return $this->redirect(['index', 'user_id' => $model->user_id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }
        }
        return $this->render('activate-form', compact('model', 'paramsModel'));
    }

    public function actionToggleRenew($id, $value = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = $this->findModel($id);
        if (!$model->renewable) {
            throw new ForbiddenHttpException(Yii::t('app', 'The tariff is not renewable.'));
        }
        if (!isset($value)) {
            $value = $model->renew ? 0 : 1;
        }
        $model->renew = $value;
        if ($model->save()) {
            if ($model->renew) {
                $this->clearRenewFlag($user->id, $model->id);
            }
            FlashHelper::setFlash('success',
                Yii::t('app', 'Auto renew for tariff "{tariff}" {renew, plural, =0{disabled} other{enabled}}.',
                    ['tariff' => $model->title, 'renew' => $model->renew]));
        } else {
            FlashHelper::setFlash('error', Yii::t('app', 'Saving auto renew option failed.'));
        }
        return $this->redirect(['index', 'user_id' => $model->user_id]);
    }

    /**
     * Lists all UserTariff models.
     * @param int|null $user_id
     * @return mixed
     */
    public function actionIndex($user_id = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($user->isAdmin && isset($user_id) && ($u = User::findOne($user_id))) {
            $user = $u;
        }
        Tariff::userGetActive($user);

        $active = Tariff::userGetActiveQuery($user);

        $ready = UserTariff::find()
            ->where(['user_id' => $user->id, 'status' => UserTariff::STATUS_READY])
            ->orderBy(['renew' => SORT_DESC]);
        $unpaid = UserTariff::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['>=', 'status', 0])
            ->andWhere(['<', 'status', UserTariff::STATUS_READY]);
        $finished = UserTariff::find()
            ->where(['user_id' => $user->id, 'status' => UserTariff::STATUS_FINISHED])
            ->andWhere(['<', 'status', 0])
            ->orderBy(['id' => SORT_DESC]);

        return $this->render('index', compact('active', 'ready', 'unpaid', 'finished', 'user'));
    }

    /**
     * Displays a single UserTariff model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param UserTariff $model
     * @return string|\yii\web\Response
     */
    public function modelForm(UserTariff $model)
    {
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new UserTariff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserTariff();
        return $this->modelForm($model);
    }

    /**
     * Updates an existing UserTariff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        return $this->modelForm($model);
    }

    /**
     * Deletes an existing UserTariff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = $this->findModel($id);
        if (!$user->isAdmin && $model->status >= UserTariff::STATUS_READY || $model->status < 0) {
            throw new ForbiddenHttpException(Yii::t('app', 'The tariff can not be deleted.'));
        }
        try {
            $model->delete();
        } catch (IntegrityException $e) {
            FlashHelper::setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index', 'user_id' => $model->user_id]);
    }

    public function actionFinish($id)
    {
        $model = $this->findModel($id);
        $model->finish();
        return $this->redirect(['index', 'user_id' => $model->user_id]);
    }

    /**
     * Finds the UserTariff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return UserTariff the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        /** @var \app\models\UserTariff|null $model */
        if (($model = UserTariff::findOne($id)) !== null) {
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested tariff.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested tariff does not exist.'));
        }
    }
}
