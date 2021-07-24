<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use flyiing\helpers\FlashHelper;
use Yii;
use app\models\ClientQuery;
use app\models\ClientQuerySearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ClientQueryController implements the CRUD actions for ClientQuery model.
 */
class ClientQueryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['admin'],
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
                ],
            ],
        ];
    }

    public function actionAdmin()
    {
        $searchModel = new ClientQuerySearch();
        $params = Yii::$app->request->queryParams;
        $params['ClientQuerySearch']['groupBy'] = ArrayHelper::getValue($params, 'ClientQuerySearch.groupBy', 'all');
        $dataProvider = $searchModel->search($params);

        return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all ClientQuery models.
     * @return mixed
     */
    public function actionIndex()
    {
        /* @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $searchModel = new ClientQuerySearch();
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
     * Displays a single ClientQuery model.
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
     * Deletes an existing ClientQuery model.
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
     * Finds the ClientQuery model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientQuery the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = ClientQuery::findOne($id)) !== null) {
            /* @var \app\models\ClientQuery $model */
            /* @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested query.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested query does not exist.'));
        }
    }
}
