<?php

namespace app\controllers;

use app\helpers\DataHelper;
use app\models\ClientQuery;
use app\models\variable\UNotify;
use Yii;
use app\models\ClientSite;
use app\models\ClientSiteSearch;
use app\helpers\ModelsHelper;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * ClientSiteController implements the CRUD actions for ClientSite model.
 */
class ClientSiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'select-list'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
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
            $query = ClientSite::find()->orderBy(['title' => SORT_ASC]);
            if (!$user->isAdmin) {
                $query->andWhere(['user_id' => $user->getSubjectUsers()->select('id')]);
            }
            if (isset($pid) && $pid > 0) {
                $query->andWhere(['user_id' => $pid]);
            }
            $output = ModelsHelper::getSelectData($query->all());
        }
        return ['output' => $output];
    }

    /**
     * Lists all ClientSite models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $params = Yii::$app->request->queryParams;
        $searchModel = new ClientSiteSearch();
        if (!$user->isAdmin) {
            $searchModel->subjectUsersIds = $user->getSubjectUsers()->select('id');
        }
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ClientSite model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($warning = ModelsHelper::userMasterWarnings(null, $model)) {
            FlashHelper::setFlash('warning', $warning);
        }
        return $this->render('view', compact('model'));
    }

    /**
     * @param ClientSite $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(ClientSite $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $notifyModel = new UNotify([
            'user_id' => $model->user_id,
            'site_id' => $model->isNewRecord ? -1 : $model->id,
        ]);
        $post = Yii::$app->request->post();
        $oldDomain = $model->domain;
        if ($model->load($post)) {

            if (!$user->checkSubject($model->user_id)) {
                throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if (!($user->isAdmin || $model->isNewRecord || $oldDomain == DataHelper::getDomain($model->url))) {
                if ($model->getClientQueries()->where(['>=', 'status', ClientQuery::STATUS_COMM_SUCCESS])->exists()) {
                    $msg = Yii::t('app', 'You can not change the domain for this site since there are some queries for it.');
                    throw new ForbiddenHttpException($msg);
                }
            }

            if ($model->save()) {
                $notifyModel->user_id = $model->user_id;
                $notifyModel->site_id = $model->id;
                if ($notifyModel->load($post)) {
                    $notifyModel->save();
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
            'notifyModel' => $notifyModel,
        ]);
    }

    /**
     * Creates a new ClientSite model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = new ClientSite(['user_id' => $user->id]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientSite model.
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
     * Deletes an existing ClientSite model.
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
     * Finds the ClientSite model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientSite the loaded model
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientSite::findOne($id)) !== null) {
            /** @var \app\models\ClientSite $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested website.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested website does not exist.'));
        }
    }
}
