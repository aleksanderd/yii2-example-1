<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use kartik\grid\GridView;
use flyiing\grid\ActionColumn;
use flyiing\helpers\UniHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientQueryTestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Query tests');
$this->params['breadcrumbs'][] = Html::icon('client-query-test') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add query test')
    ]
]);

echo HintWidget::widget(['message' => '#ClientQueryTestIndex.hint']);
echo '<div class="client-query-test-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
];
if (Yii::$app->user->identity->isAdmin) {
    $columns[] = [
        'label' => Yii::t('user', 'User'),
        'content' => function($model) {
            if ($user = $model->user) {
                return $user->username;
            } else {
                return '-';
            }
        },
    ];
}

$columns = array_merge($columns, [
    [
        'class' => 'flyiing\grid\DataColumn',
        'template' => '<b>{title}</b><br><small>{description}</small>',
        'attributes' => ['title', 'description'],
    ],
    [
        'class' => 'flyiing\grid\ActionColumn',
        'template' => '<div class="btn-group btn-group-sm" role="group">{run}{view}{update}</div> {delete}',
        'buttons' => [
            'run' => function($url, $model, $key) {
                return ActionColumn::renderDefaultButton(Html::icon('run') . Yii::t('app', 'Run'),
                    $url, $model, $key, ['target' => '_blank']);
            },
        ],
    ],
]);

echo GridView::widget([
    'hover' => true,
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,

    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="client-query-test-index"
