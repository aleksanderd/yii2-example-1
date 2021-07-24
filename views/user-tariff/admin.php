<?php

use app\helpers\DataHelper;
use app\helpers\ViewHelper;
use app\models\Tariff;
use app\models\UserTariff;
use app\widgets\grid\GridView;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserTariffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'User tariffs admin');
$this->params['breadcrumbs'][] = Html::icon('tariff') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
//    'create' => [
//        'label' => Yii::t('app', 'Add tariff'),
//        'url' => ['select', 'user_id' => $user->id],
//    ],
    'admin' => [
        'label' => Yii::t('app', 'User tariffs'),
        'url' => ['index'],
        'options' => ['class' => 'btn-default']
    ],
]);

echo HintWidget::widget(['message' => '#UserTariffAdmin.hint']);
echo '<div class="user-tariff-admin">' . PHP_EOL;
echo AlertFlash::widget();
echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'class' => \app\widgets\grid\UserColumn::className(),
        'filterWidgetOptions' => [
            'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        ],
        //'width' => '20%',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'headerTemplate' => '{tariff_id}',
        'filterTemplate' => '{tariff_id}',
        'template' => '<strong>{tariff_id}</strong><br><small>{tariff.desc}<br>{renew}</small>',
        'attributes' => [
            [
                'attribute' => 'tariff_id',
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    //'model' => $searchModel,
                    'data' => ArrayHelper::map(Tariff::find()->orderBy(['title' => SORT_ASC])->all(), 'id', 'title'),
                    'hideSearch' => true,
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => Yii::t('app', 'All tariffs'),
                    ],
                ],
                'content' => function(UserTariff $m) {
                    return $m->tariff->title;
                },
            ],
            [
                'attribute' => 'tariff.desc',
                'filter' => false,
            ],
            [
                'attribute' => 'renew',
                'format' => 'raw',
                'filter' => false,
                'value' => function (UserTariff $m) {
                    if (!$m->renewable || $m->status < 0) {
                        return '';
                    }
                    $content = '';
                    if ($m->renew) {
                        $aLabel = Yii::t('app', 'disable');
                        $content .= Html::tag('span', Yii::t('app', 'Auto renew enabled'), [
                            'class' => 'label label-info'
                        ]);
                    } else {
                        $aLabel = Yii::t('app', 'enable');
                        $content .= Yii::t('app', 'Auto renew disabled');
                    }
                    if ($m->status >= UserTariff::STATUS_READY) {
                    }
                    $content .= ' (' . Html::a($aLabel, ['toggle-renew', 'id' => $m->id, 'value' => $m->renew ? 0 : 1]) .')';
                    return $content;
                },
            ]
        ],
    ],
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'filterWidgetOptions' => [
            'data' => UserTariff::statusLabels(),
            'hideSearch' => true,
            'pluginOptions' => [
                'allowClear' => true,
                'placeholder' => Yii::t('app', 'All statuses'),
            ],
        ],
        'content' => function(UserTariff $m) {
            return ViewHelper::userTariffStatusSpan($m);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{price}</strong><br>{lifetime}<br>{renewable}',
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'price',
                'format' => 'currency',
            ],
            [
                'attribute' => 'lifetime',
                'value' => function (UserTariff $m) {
                    return $m->getAttributeLabel('lifetime') .': '. Tariff::getLifetimeReadable($m);
                },

            ],
            [
                'attribute' => 'renewable',
                'value' => function (UserTariff $m) {
                    return $m->getAttributeLabel('renewable') .': '. Tariff::getRenewableReadable($m);
                },
            ],
        ],
    ],
    [
        'visible' => false,
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => Yii::t('app', 'Minutes') . ': {seconds_used} '. Yii::t('app', 'of') .' {seconds}<br>' .
            Yii::t('app', 'Messages') . ': {messages_used} '. Yii::t('app', 'of') .' {messages}',
        'headerTemplate' => 'Used',
        'attributes' => [
            [
                'label' => Yii::t('app', 'Minutes'),
                'attribute' => 'seconds',
                'value' => function (UserTariff $m) {
                    return intval($m->seconds) > 0 ?
                        DataHelper::durationToText($m->seconds) .' ' : Yii::t('app', 'unlimited');
                },
            ],
            [
                'label' => Yii::t('app', 'Used'),
                'attribute' => 'seconds_used',
                'value' => function (UserTariff $m) {
                    return DataHelper::durationToText($m->seconds_used);
                },
            ],
            [
                'attribute' => 'messages',
                'value' => function (UserTariff $m) {
                    return intval($m->messages) > 0 ? $m->messages : Yii::t('app', 'unlimited');
                },
            ],
            [
                'label' => Yii::t('app', 'Used'),
                'attribute' => 'messages_used',
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
//        'filter' => \kartik\daterange\DateRangePicker::widget([
//            'model' => $searchModel,
//            'attribute' => 'dateRange',
//            'presetDropdown' => true,
//        ]),
        'attributes' => [
            [
                'attribute' => 'started_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'lifetimeEnd',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'finished_at',
                'format' => 'datetime',
            ],
        ],
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>';
