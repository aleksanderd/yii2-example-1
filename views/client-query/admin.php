<?php

use app\helpers\DataHelper;
use app\models\ClientQuery;
use app\models\ClientQuerySearch;
use app\widgets\grid\GridView;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientQuerySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Queries administration');
$this->params['breadcrumbs'][] = Html::icon('client-query') . $this->title;

echo HintWidget::widget(['message' => '#ClientQueryAdmin.hint']);
echo '<div class="client-query-admin">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', ['model' => $searchModel]);

$columns = [
//    [
//        'attribute' => 'record_time',
//        'content' => function (ClientQuery $m) {
//            return DataHelper::durationToText($m->record_time);
//        },
//        'hAlign' => 'center',
//    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'client_cost',
            ],
            [
                'attribute' => 'cost',
                'format' => ['decimal', 2],
            ],
        ],
        'hAlign' => 'right',
    ],
];

if (!$searchModel->isGrouped) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\IdColumn::className(),
        ],
        [
            'attribute' => 'status',
            'content' => function($m) {
                return $this->render('_record_url', ['model' => $m]);
            },
            'hAlign' => 'left',
            'vAlign' => 'top',
        ],
    ], $columns);
}

if (!$searchModel->isGrouped || $searchModel->isDatetimeGrouped) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\DateTimeColumn::className(),
            'period' => $searchModel->groupBy,
        ],
    ], $columns);
}

if (!$searchModel->isGrouped || $searchModel->groupBy == ClientQuerySearch::GROUP_BY_USER_SITE) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\SiteColumn::className()
        ],
    ], $columns);
}

if (!$searchModel->isGrouped || $searchModel->groupBy == ClientQuerySearch::GROUP_BY_USER
    || $searchModel->groupBy == ClientQuerySearch::GROUP_BY_USER_SITE) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className()
        ],
    ], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'condensed' => true,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>';
