<?php

use app\helpers\ViewHelper;
use app\models\STicket;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\STicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Support tickets');
//$this->params['wrapperClass'] = 'gray-bg';
$this->params['breadcrumbs'][] = Html::icon('sticket') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add support ticket'),
    ]
]);

echo '<div class="sticket-index">' . PHP_EOL;

echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),

        'attributes' => [
            [
                'attribute' => 'id',
            ],
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{title}</strong><br><small>{topic_id}</small>',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function(STicket $m) {
                    return Html::a($m->title, ['view', 'id' => $m->id], [
                        'pjax' => 0,
                    ]);
                },
            ],
            [
                'attribute' => 'topic_id',
                'content' => function(STicket $m) {
                    return ArrayHelper::getValue(STicket::topicLabels(), $m->topic_id, Yii::t('app', 'Unknown topic'));
                },
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'format' => 'datetime',
        'attributes' => [
            'created_at',
            'updated_at',
        ],
        'hAlign' => 'center',
    ],
    [
        'attribute' => 'status',
        'content' => function(STicket $m) {
            return ViewHelper::ticketStatusSpan($m);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
];

if ($user->isAdmin) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
        ],
    ], $columns);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    //'bordered' => false,

]);

echo '</div>' . PHP_EOL; // class="sticket-index"
