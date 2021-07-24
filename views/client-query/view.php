<?php

use app\helpers\DataHelper;
use flyiing\helpers\Html;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQuery */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'View query #') . $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-query') . Yii::t('app', 'Queries'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;

echo '<div class="client-query-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    [
        'label' => Yii::t('app', 'Website'),
        'format' => 'raw',
        'value' => $model->site_id ?
            Html::a($model->site->title, ['client-site/view', 'id' => $model->site_id]) : '-',
    ],
    'id',
    'url:url',
    'at:datetime',
    [
        'attribute' => 'visit_time',
        'value' => DataHelper::durationToText($model->visit_time),
    ],
    [
        'attribute' => 'hit_time',
        'value' => DataHelper::durationToText($model->hit_time),
    ],
    [
        'attribute' => 'trigger',
        'value' => Yii::t('app', 'tr_' . DataHelper::triggerId($model->trigger, true)),
    ],
    'call_info',
    [
        'label' => Yii::t('app', 'Rule'),
        'format' => 'raw',
        'value' => $model->rule_id ?
            Html::a($model->rule->title, ['client-rule/view', 'id' => $model->rule_id]) : '-',
    ],
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => $this->render('_status_label', compact('model')),
    ],
    [
        'label' => Yii::t('app', 'Record'),
        'format' => 'raw',
        'value' => $this->render('_record_url', compact('model')),
    ],
//    'result.msg',

    [
        'attribute' => 'userTariff.title',
        'label' => Yii::t('app', 'Tariff'),
    ],
    [
        'attribute' => 'client_cost',
        'format' => 'currency',
    ],

];

if ($model->test) {
    $attributes = array_merge([
        [
            'label' => Yii::t('app', 'Test'),
            'value' => $model->test->title,
        ],
    ], $attributes);
}

if ($user->isAdmin) {
    $attributes = array_merge([
        [
            'label' => Yii::t('user', 'User'),
            'value' => $model->user->username,
        ],
    ], $attributes, [
        [
            'attribute' => 'cost',
        ],
    ]);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

$callsQuery = $model->getCalls();
if ($callsQuery->count() > 0) {
    echo Html::tag('h3', Yii::t('app', 'Calls'));
    $dp = new \yii\data\ActiveDataProvider(['query' => $callsQuery]);
    $columns = [
        [
            'attribute' => 'id',
            'hAlign' => 'right',
        ],
        [
            'attribute' => 'started_at',
            'format' => 'datetime',
            'hAlign' => 'center',
        ],
        ['class' => \app\widgets\grid\CallLineColumn::className()],
        ['class' => \app\widgets\grid\CallResultColumn::className()],
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'format' => 'currency',
            'attributes' => [
                'client_price',
                'client_cost',
            ],
        ],
    ];
    if ($user->isAdmin) {
        $columns[] = [
            'attribute' => 'cost',
        ];
    }
    echo \app\widgets\grid\GridView::widget([
        'dataProvider' => $dp,
        'columns' => $columns,
    ]);
}

echo '</div>' . PHP_EOL; // class="client-query-view"
