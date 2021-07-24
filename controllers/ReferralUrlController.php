<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use Yii;
use app\models\ReferralUrl;
use app\models\ReferralUrlSearch;
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
 * ReferralUrlController implements the CRUD actions for ReferralUrl model.
 */
class ReferralUrlController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
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
                    'delete-safe' => ['post'],
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

    /**
     * Lists all ReferralUrl models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $defaultUrl = ReferralUrl::defaultReferralUrl($user->id);
        $searchModel = new ReferralUrlSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['ReferralUrlSearch']['user_id'] = $user->id;
            $params['ReferralUrlSearch']['status'] = ReferralUrl::STATUS_ENABLED;
        }
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'defaultUrl' => $defaultUrl,
        ]);
    }

    /**
     * Displays a single ReferralUrl model.
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
     * @param ReferralUrl $model
     * @return string|\yii\web\Response
     */
    public function modelForm(ReferralUrl $model)
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
     * Creates a new ReferralUrl model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = new ReferralUrl([
            'user_id' => $user->id,
            'status' => ReferralUrl::STATUS_ENABLED,
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ReferralUrl model.
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
     * Deletes an existing ReferralUrl model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $defaultModel = ReferralUrl::defaultReferralUrl($model->user_id);
        if ($model->id === $defaultModel->id) {
            throw new BadRequestHttpException(Yii::t('app', 'Can not delete default referral url.'));
        }
        $model->status = ReferralUrl::STATUS_DELETED;
        $model->title = sprintf('#%d ', $model->id). $model->title;
        if ($model->save(false, ['status', 'title'])) {
            FlashHelper::setFlash('success',
                Yii::t('app', 'Referral url "{title}" moved to trash.', ['title' => $model->title]));
        } else {
            FlashHelper::setFlash('error', Yii::t('app', 'Referral url moving to trash failed.'));
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the ReferralUrl model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ReferralUrl the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ReferralUrl::findOne($id)) !== null) {
            /** @var \app\models\ReferralUrl $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested referral url.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested referral url does not exist.'));
        }
    }
}
