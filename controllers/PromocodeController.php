<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use Yii;
use app\models\Promocode;
use app\models\PromocodeSearch;
use app\models\PromocodeInputForm;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\filters\AccessControl;
use flyiing\helpers\FlashHelper;

/**
 * PromocodeController implements the CRUD actions for Promocode model.
 */
class PromocodeController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'activate' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'activate', 'select-list'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user && $user->isAdmin;
                        },
                    ],
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
        return true;
    }

    public function actionSelectList()
    {
        /* @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        // $pid - parent id (ID пользователя)
        Yii::$app->response->format = Response::FORMAT_JSON;
        $output = [];
        if ($parents = ArrayHelper::getValue(Yii::$app->request->post(), 'depdrop_parents')) {
            $pid = end($parents);
            if (!$user->isAdmin) {
                $pid = $user->id;
            }
            if (isset($pid) && $pid > 0) {
                $output = ModelsHelper::getSelectData(Promocode::findAll(['user_id' => $pid]), ['id', 'name' => 'code']);
            }
        }
        return ['output' => $output];
    }

    /**
     * Lists all Promocode models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new PromocodeSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params[$searchModel->formName()]['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);
        $inputForm = new PromocodeInputForm();

        return $this->render('index', [
            'inputForm' => $inputForm,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Promocode model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @param Promocode $model
     * @return string|\yii\web\Response
     */
    public function modelForm(Promocode $model)
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
     * Creates a new Promocode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Promocode();
        return $this->modelForm($model);
    }

    /**
     * Updates an existing Promocode model.
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
     * Deletes an existing Promocode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Activate promocode.
     * If activation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionActivate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $inputForm = new PromocodeInputForm();
        $params = Yii::$app->request->post();
        $params[$inputForm->formName()]['user_id'] = $user->id;
        if ($inputForm->load($params)) {
            if ($inputForm->validate()) {
                if ($inputForm->activate()) {
                    FlashHelper::setFlash('success', Yii::t('app', 'Promotional code {code} has been successfully activated. Your balance increased by {amount}', [
                        'code' => $inputForm->code,
                        'amount' => Yii::$app->formatter->asCurrency($inputForm->promocode->amount),
                    ]));
                } else {
                    FlashHelper::setFlash('error', Yii::t('app', 'Promocode activation error.'));
                }
            } else {
                foreach ($inputForm->errors as $error) {
                    FlashHelper::setFlash('error', $error);
                }
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Promocode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return Promocode the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = Promocode::findOne($id)) !== null) {
            /** @var \app\models\Promocode $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested promocode.'));
            }
        } else {
            throw new NotFoundHttpException('The requested promocode does not exist.');
        }
    }
}
