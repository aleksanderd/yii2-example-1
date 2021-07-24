<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use app\models\ClientSite;
use app\models\variable\UNotify;
use Yii;
use app\models\ClientPage;
use app\models\ClientPageSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * ClientPageController implements the CRUD actions for ClientPage model.
 */
class ClientPageController extends Controller
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
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        // $pid - parent id (ID сайта)
        Yii::$app->response->format = Response::FORMAT_JSON;
        $output = [];
        if ($parents = ArrayHelper::getValue(Yii::$app->request->post(), 'depdrop_parents')) {
            if ($site = ClientSite::findOne(end($parents))) {
                /** @var ClientSite $site */
                if ($user->checkSubject($site->user_id)) {
                    $output = ModelsHelper::getSelectData($site->clientPages);
                }
            }
        }
        return ['output' => $output];
    }

    /**
     * Lists all ClientPage models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $searchModel = new ClientPageSearch();
        if (!$user->isAdmin) {
            $searchModel->subjectUsersIds = $user->getSubjectUsers()->select('id');
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ClientPage model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param ClientPage $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(ClientPage $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $notifyModel = new UNotify([
            'user_id' => $model->user_id,
            'site_id' => $model->isNewRecord ? -1 : $model->site_id,
            'page_id' => $model->isNewRecord ? -1 : $model->id,
        ]);
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (!$user->checkSubject($model->user_id)) {
                throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                $notifyModel->user_id = $model->user_id;
                $notifyModel->site_id = $model->site_id;
                $notifyModel->page_id = $model->id;
                if ($notifyModel->load($post)) {
                    $notifyModel->save();
                }
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', compact('model', 'notifyModel'));
    }

    /**
     * Creates a new ClientPage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int $site_id
     * @return mixed
     */
    public function actionCreate($site_id = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $user_id = $user->id;
        if ($site_id > 0 && ($site = ClientSite::findOne($site_id))) {
            /** @var \app\models\ClientSite $site */
            if ($site->user_id != $user->id) {
                if ($user->isAdmin) {
                    $user_id = $site->user_id;
                } else {
                    $site_id = null;
                }
            }
        } else {
            $site_id = null;
        }
        $model = new ClientPage(compact('user_id', 'site_id'));
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientPage model.
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
     * Deletes an existing ClientPage model.
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
     * Finds the ClientPage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientPage the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientPage::findOne($id)) !== null) {
            /** @var \app\models\ClientPage $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested page.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
