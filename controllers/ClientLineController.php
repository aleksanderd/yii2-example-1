<?php

namespace app\controllers;

use Yii;
use app\models\ClientLine;
use app\models\ClientLineSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;
use app\helpers\ModelsHelper;

/**
 * ClientLineController implements the CRUD actions for ClientLine model.
 */
class ClientLineController extends Controller
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
        // $pid - parent id (ID пользователя)
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $pid = ArrayHelper::getValue($post, 'user_id');
        if ($parents = ArrayHelper::getValue($post, 'depdrop_parents')) {
            $pid = end($parents);
        }
        $query = ClientLine::find();
        if (!$user->isAdmin) {
            $query->andWhere(['user_id' => $user->getSubjectUsers()->select('id')]);
        }
        if (isset($pid) && $pid > 0) {
            $query->andWhere(['user_id' => $pid]);
        }
        $output = ModelsHelper::getSelectData($query->all(), [
            'id', 'name' => 'title', 'info'
        ]);
        return ['output' => $output];
    }

    /**
     * Lists all ClientLine models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $searchModel = new ClientLineSearch();
        $params = Yii::$app->request->queryParams;
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
     * Displays a single ClientLine model.
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
     * @param ClientLine $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(ClientLine $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
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
     * Creates a new ClientLine model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = new ClientLine(['user_id' => $user->id]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientLine model.
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
     * Deletes an existing ClientLine model.
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
     * Finds the ClientLine model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientLine the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientLine::findOne($id)) !== null) {
            /** @var \app\models\ClientLine $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested phone line.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested phone line does not exist.'));
        }
    }
}
