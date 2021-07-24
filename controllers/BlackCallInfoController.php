<?php

namespace app\controllers;

use Yii;
use app\models\BlackCallInfo;
use app\models\BlackCallInfoSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * BlackCallInfoController implements the CRUD actions for BlackCallInfo model.
 */
class BlackCallInfoController extends Controller
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
     * Lists all BlackCallInfo models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new BlackCallInfoSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['BlackCallInfoSearch']['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BlackCallInfo model.
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
     * @param BlackCallInfo $model
     * @return string|\yii\web\Response
     */
    public function modelForm(BlackCallInfo $model)
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
     * Creates a new BlackCallInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param string|null $call_info
     * @return mixed
     */
    public function actionCreate($call_info = null)
    {
        $model = new BlackCallInfo(compact('call_info'));
        return $this->modelForm($model);
    }

    /**
     * Updates an existing BlackCallInfo model.
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
     * Deletes an existing BlackCallInfo model.
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
     * Finds the BlackCallInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return BlackCallInfo the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        /** @var \app\models\BlackCallInfo|null $model */
        if (($model = BlackCallInfo::findOne($id)) !== null) {
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested black call info.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested black call info does not exist.'));
        }
    }
}
