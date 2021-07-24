<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use app\models\forms\BasePeriodFilter;
use flyiing\helpers\FlashHelper;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\ClientQuerySearch;
use app\models\ConversionSearch;
use yii\web\ForbiddenHttpException;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'conversion'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $params = Yii::$app->request->queryParams;
        $searchModel = new BasePeriodFilter([
            'user_id' => $user->isAdmin ? null : $user->id,
            'period' => 30,
        ]);
        $searchModel->load($params);
        if (!$user->checkSubject($searchModel->user_id)) {
            throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
        }
        $conversionSearch = new ConversionSearch([
            'groupBy' => ConversionSearch::GROUP_BY_USER_SITE,
            'dtStart' => strtotime(sprintf('%d days ago', $searchModel->period)),
            'dtEnd' => time(),
        ]);
        if (!$user->isAdmin) {
            $conversionSearch->subjectUsersIds = $user->getSubjectUsers()->select('id');
        }
        $conversionSearch->user_id = $searchModel->user_id;
        $conversionSearch->site_id = $searchModel->site_id;
        return $this->render('index', compact('searchModel', 'conversionSearch'));
    }

    public function actionIndex2()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($warning = ModelsHelper::userMasterWarnings()) {
            FlashHelper::setFlash('warning', $warning);
        }
        $params = Yii::$app->request->queryParams;
        $filterModel = new ClientQuerySearch();
        $filterModel->load($params);

        $conversionSearch = new ConversionSearch([
            'groupBy' => ConversionSearch::GROUP_BY_USER_SITE,
            'dtStart' => strtotime('30 days ago'),
            'dtEnd' => time(),
        ]);
        $conversionSearch->load($params, 'ClientQuerySearch');
        if (!$user->isAdmin) {
            $filterModel->user_id = $user->id;
            $conversionSearch->user_id = $user->id;
        }
        return $this->render('index', compact('filterModel', 'conversionSearch'));
    }

    public function actionConversion()
    {
        $this->redirect(['/conversion/index']);
    }

}
