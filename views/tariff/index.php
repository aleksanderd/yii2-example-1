<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;
use app\models\Tariff;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TariffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tariffs');
$this->params['breadcrumbs'][] = Html::icon('tariff') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add tariff'),
    ]
]);

echo HintWidget::widget(['message' => '#TariffIndex.hint']);
echo '<div class="tariff-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    'id',
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            'title',
            'desc',
            [
                'attribute' => 'status',
                'value' => function (Tariff $m) {
                    return Tariff::statusLabels()[$m->status];
                },
            ],
        ],
        'template' => '<strong>{title}</strong><br>{desc}<br><small>{status}</small>',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{price}</strong><br>{lifetime}<br>{renewable}',
        'attributes' => [
            [
                'attribute' => 'price',
                'format' => 'currency',
            ],
            [
                'attribute' => 'lifetime',
                'value' => function (Tariff $m) {
                    return $m->getAttributeLabel('lifetime') .': '. Tariff::getLifetimeReadable($m);
                },

            ],
            [
                'attribute' => 'renewable',
                'value' => function (Tariff $m) {
                    return $m->getAttributeLabel('renewable') .': '. Tariff::getRenewableReadable($m);
                },
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
//            'queries',
            [
                'attribute' => 'minutes',
                'value' => function (Tariff $m) {
                    $content = '';
                    $content .= $m->getAttributeLabel('minutes') .': ';
                    $content .= intval($m->minutes) > 0 ? $m->minutes .' '. Yii::t('app', 'minutes') : Yii::t('app', 'Unlimited');
                    return $content;
                },
            ],
            [
                'attribute' => 'messages',
                'value' => function (Tariff $m) {
                    $content = '';
                    $content .= $m->getAttributeLabel('messages') .': ';
                    $content .= intval($m->messages) > 0 ? $m->messages .' '. Yii::t('app', 'messages') : Yii::t('app', 'Unlimited');
                    return $content;
                },
            ],
//            'space'
        ],

    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            ['attribute' => 'created_at', 'format' => 'datetime'],
            ['attribute' => 'updated_at', 'format' => 'datetime'],
        ],
    ],
//    'desc_details',
//    'desc_internal',
    ['class' => 'flyiing\grid\ActionColumn'],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="tariff-index"
