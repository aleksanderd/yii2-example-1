<?php

namespace app\controllers;

use Yii;
use app\models\ClientQueryTest;
use app\models\ClientQueryTestSearch;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * ClientQueryTestController implements the CRUD actions for ClientQueryTest model.
 */
class ClientQueryTestController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'run', 'widget'],
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
     * Lists all ClientQueryTest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ClientQueryTestSearch();
        $params = Yii::$app->request->queryParams;
        if (!Yii::$app->user->identity->isAdmin) {
            $params['ClientQueryTestSearch']['user_id'] = Yii::$app->user->identity->id;
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRun($id)
    {
        $model = $this->findModel($id);
        $query = $model->getClientQuery();
        $debug = ['process' => []];
        $rule = $query->findRule(true, $debug);
        $query->at = time();
        $query->updateTime()->save();
        $query->process($debug['process']);
        $query->updateTime()->save();
        return $this->render('run', compact('model', 'rule', 'query', 'debug'));
    }

    /**
     * Displays a single ClientQueryTest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionWidget()
    {
        return $this->render('widget');
    }

    /**
     * @param ClientQueryTest $model
     * @return string|\yii\web\Response
     */
    public function modelForm(ClientQueryTest $model)
    {
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (!Yii::$app->user->identity->isAdmin) {
                $model->user_id = Yii::$app->user->identity->id;
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
     * Creates a new ClientQueryTest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ClientQueryTest(['user_id' => Yii::$app->user->identity->id]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientQueryTest model.
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
     * Deletes an existing ClientQueryTest model.
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
     * Finds the ClientQueryTest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientQueryTest the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientQueryTest::findOne($id)) !== null) {
            if (Yii::$app->user->identity->isAdmin || Yii::$app->user->identity->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested query test.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
