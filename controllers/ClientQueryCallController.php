<?php

namespace app\controllers;

use app\models\ClientQueryCall;
use app\models\ClientQueryCallSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

/**
 * ClientQueryController implements the CRUD actions for ClientQuery model.
 */
class ClientQueryCallController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all ClientQuery models.
     * @return mixed
     */
    public function actionIndex()
    {
        /* @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new ClientQueryCallSearch();
        if (!$user->isAdmin) {
            $searchModel->subjectUsersIds = $user->getSubjectUsers()->select('id');
        }
        $params = Yii::$app->request->queryParams;
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
     * Finds the ClientQuery model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return ClientQueryCall the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        /** @var \app\models\ClientQueryCall $model */
        if (($model = ClientQueryCall::findOne($id)) !== null) {
            /* @var \app\models\ClientQueryCall $model */
            /* @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested call.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested query call does not exist.'));
        }
    }
}
