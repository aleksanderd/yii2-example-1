<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use flyiing\helpers\FlashHelper;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\ConversionSearch;

class ConversionController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'index2', 'triggers'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    protected function preAction()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $searchModel = new ConversionSearch([
            'user_id' => $user->isAdmin ? null : $user->id,
            'groupBy' => ConversionSearch::GROUP_BY_USER_SITE,
        ]);
        if (!$user->isAdmin) {
            $searchModel->subjectUsersIds = $user->getSubjectUsers()->select('id');
        }
        return $searchModel;
    }

    public function actionIndex()
    {
        $searchModel = $this->preAction();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('index', compact('dataProvider', 'searchModel'));
    }

    public function actionIndex2()
    {
        $searchModel = $this->preAction();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('index2', compact('dataProvider', 'searchModel'));
    }

    public function actionTriggers()
    {
        $searchModel = $this->preAction();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('triggers', compact('dataProvider', 'searchModel'));
    }

}
