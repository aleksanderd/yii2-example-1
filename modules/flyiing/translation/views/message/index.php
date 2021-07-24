<?php

use flyiing\helpers\Html;
//use flyiing\helpers\UniHelper;
use flyiing\translation\models\TMessage;
use flyiing\widgets\AlertFlash;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel flyiing\translation\models\TMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Translations');
$this->params['breadcrumbs'][] = Html::icon('t-message') . $this->title;
//$this->params['actions'] = UniHelper::getModelActions([
//    'create' => [
//        'label' => Yii::t('app', 'Add translation')
//    ]
//]);

echo '<div class="t-message-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'source.category',
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'source.message',
    ],
    [
        'attribute' => 'language',
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'translation',
    ],
    [
        'label' => '',
        'content' => function (TMessage $model) {
            $label = Html::icon('model-update') . Yii::t('app', 'Update');
//            return Html::a($label, ['update', 'id' => $model->id, 'language' => $model->language], [
//                'class' => 'btn-xs btn-primary'
//            ]);
            return Html::a($label, ['edit', 'category' => $model->source->category, 'message' => $model->source->message], [
                'class' => 'btn-xs btn-primary',
                'target' => '_blank',
            ]);
        },
        'hAlign' => 'center',
    ],
];

echo GridView::widget([
    'hover' => true,
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL;
