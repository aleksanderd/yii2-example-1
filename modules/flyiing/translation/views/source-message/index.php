<?php

use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel flyiing\translation\models\TSourceMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Source messages');
$this->params['breadcrumbs'][] = Html::icon('t-source-message') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add source message')
    ]
]);

echo '<div class="t-source-message-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'category',
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'message',
    ],
    [
        //'class' => 'flyiing\grid\ActionColumn',
        'class' => \yii\grid\ActionColumn::className(),
    ],
];

echo GridView::widget([
    'hover' => true,
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL;
