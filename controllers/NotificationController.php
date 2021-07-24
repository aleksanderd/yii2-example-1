<?php
// Для разных тестов
namespace app\controllers;

use app\models\NotificationSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Notification;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class NotificationController extends Controller {

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

	public function actionIndex()
	{
        /* @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new NotificationSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['NotificationSearch']['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
	}

	public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @param array $options
     * @return object
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = Notification::findOne($id)) !== null) {
            /** @var Notification $model */
            /* @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested notification.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested notification does not exist.'));
        }
    }
}
