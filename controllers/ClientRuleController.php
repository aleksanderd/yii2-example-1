<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use app\models\ClientSite;
use Yii;
use app\models\ClientRule;
use app\models\ClientRuleSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * ClientRuleController implements the CRUD actions for ClientRule model.
 */
class ClientRuleController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'view', 'update', 'delete'],
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

    /**
     * Lists all ClientRule models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings($user)) {
            FlashHelper::setFlash('warning', $warning);
        }
        $searchModel = new ClientRuleSearch();
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
     * Displays a single ClientRule model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @param ClientRule $model
     * @return string|\yii\web\Response
     */
    public function modelForm(ClientRule $model)
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
     * Creates a new ClientRule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $user_id = $user->id;
        $params = Yii::$app->request->queryParams;
        if (($site_id = ArrayHelper::getValue($params, 'site_id')) && ($site = ClientSite::findOne($site_id))) {
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
        $model = new ClientRule(compact('user_id', 'site_id'));
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientRule model.
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
     * Deletes an existing ClientRule model.
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
     * Finds the ClientRule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientRule the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientRule::findOne($id)) !== null) {
            /** @var ClientRule $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested rule.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested rule does not exist.'));
        }
    }
}
