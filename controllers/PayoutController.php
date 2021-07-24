<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use app\models\Transaction;
use Yii;
use app\models\Payout;
use app\models\PayoutSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * PayoutController implements the CRUD actions for Payout model.
 */
class PayoutController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'retry', 'delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['status'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user && $user->isAdmin;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'status' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!ArrayHelper::getValue(Yii::$app->params, 'referrals')) {
            throw new NotFoundHttpException();
        }
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if (!($action->id == 'agreement' || ModelsHelper::userPartnerAgreed($user))) {
            return $this->redirect(['/user-referral/agreement']);
        }
        if (in_array($action->id, ['create', 'retry']) && !$user->isPayoutAllowed) {
            throw new BadRequestHttpException(Yii::t('app', 'Payout not allowed yet.'));
        }
        return true;
    }

    /**
     * @param $id
     * @param $status
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionStatus($id, $status)
    {
        $statusLabels = Payout::statusLabels();
        if (!isset($statusLabels[$status])) {
            throw new BadRequestHttpException(Yii::t('app', 'Invalid payout status.'));
        }
        $msg = '';
        $update = ['status'];
        $model = $this->findModel($id);
        if ($status == Payout::STATUS_IN_PROCESS && $model->status != Payout::STATUS_REQUEST) {
            throw new BadRequestHttpException(Yii::t('app', 'Payout not in request status.'));
        }
        if ($status == Payout::STATUS_COMPLETE) {
            if ($model->status != Payout::STATUS_IN_PROCESS) {
                throw new BadRequestHttpException(Yii::t('app', 'Payout not in process status.'));
            }
            $transaction = new Transaction([
                'user_id' => $model->user_id,
                'amount' => -1 * $model->amount,
                'description' => Yii::t('app', 'Payout #{id}', ['id' => $model->id]),
            ]);
            if ($transaction->save()) {
                $msg .= Yii::t('app', 'Your balance decreased by {amount}.', ['amount' => -1 * $transaction->amount]);
                $model->transaction_id = $transaction->id;
                $update[] = 'transaction_id';
            } else {
                throw new BadRequestHttpException(Yii::t('app', 'Transaction saving error.'));
            }
        }
        $model->status = $status;
        if ($model->save(false, $update)) {
            if (strlen($msg) > 0) {
                $msg .= '<br/>';
            }
            FlashHelper::setFlash('success', $msg . Yii::t('app', 'Payout status updated.'));
        } else {
            FlashHelper::setFlash('error', Yii::t('app', 'Payout status update failed.'));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Lists all Payout models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new PayoutSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['PayoutSearch']['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payout model.
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
     * @param Payout $model
     * @return string|\yii\web\Response
     */
    public function modelForm(Payout $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (!$user->isAdmin) {
                $model->user_id = $user->id;
            }

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
     * Creates a new Payout model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = new Payout([
            'user_id' => $user->id,
            'status' => Payout::STATUS_REQUEST,
            'amount' => $user->partnerMaxPayout,
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing Payout model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = $this->findModel($id);
        if (!($user->isAdmin || $model->isWritable)) {
            FlashHelper::setFlash('error', 'The payout is not writable.');
            return $this->redirect('index');
        }
        return $this->modelForm($model);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRetry($id)
    {
        $model = $this->findModel($id);
        if ($model->status == Payout::STATUS_COMPLETE) {
            $clone = new Payout();
            $clone->attributes = $model->attributes;
            $clone->status = Payout::STATUS_REQUEST;
            $clone->transaction_id = null;
            $clone->created_at = null;
            $model = $clone;
        } else if ($model->status == Payout::STATUS_REJECTED) {
            $model->status = Payout::STATUS_REQUEST;
            $max = $model->user->partnerMaxPayout;
            if ($model->amount > $max) {
                $model->amount = $max;
            }
        } else {
            throw new BadRequestHttpException(Yii::t('app', 'Can not retry payout with such status.'));
        }
        return $this->modelForm($model);
    }

    /**
     * Deletes an existing Payout model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = $this->findModel($id);
        if (!($model->status == Payout::STATUS_REJECTED || $user->isAdmin || $model->isWritable)) {
            FlashHelper::setFlash('error', 'The payout is not writable.');
            return $this->redirect('index');
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Payout model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return Payout the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = Payout::findOne($id)) !== null) {
            /** @var \app\models\Payout $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested payout.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested payout does not exist.'));
        }
    }
}
