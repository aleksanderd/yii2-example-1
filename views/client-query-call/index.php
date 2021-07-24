<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use app\models\ClientQueryCall;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientQueryCallSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Calls');
$this->params['breadcrumbs'][] = Html::icon('client-query-call') . $this->title;

echo HintWidget::widget(['message' => '#ClientQueryCallIndex.hint']);
echo '<div class="client-query-call-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
    [
        'label' => Yii::t('app', 'Query'),
        'content' => function (ClientQueryCall $m) {
            return Html::a('#' . $m->query_id .' '. Yii::$app->formatter->format($m->query->at, 'datetime'), ['/client-query/view', 'id' => $m->query_id]);
        },
    ],
    [
        'class' => \app\widgets\grid\CallLineColumn::className(),
    ],
    [
        'attribute' => 'started_at',
        'label' => Yii::t('app', 'At'),
        'format' => 'time',
        'hAlign' => 'right',
    ],
    [
        'class' => \app\widgets\grid\CallResultColumn::className(),
    ],
    'client_price:currency',
    'client_cost:currency',
];

if (count($user->subjectUsers) > 1) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
            'filterWidgetOptions' => [
                'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
            ],
            //'width' => '20%',
        ]
    ], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'hover' => true,
    'condensed' => true,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-query-call-index"
