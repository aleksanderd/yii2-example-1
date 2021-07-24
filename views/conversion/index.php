<?php

use app\helpers\DataHelper;
use app\models\Conversion;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\ConversionSearch */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Website conversion increase');
$this->params['breadcrumbs'][] = $this->title;

if ($user->isAdmin) {
    $this->params['actions'] = [
        'triggers' => [
            'label' => Yii::t('app', 'Triggers'),
            'url' => ['/conversion/triggers'],
            'options' => ['class' => 'btn-info']
        ],
    ];
}

echo HintWidget::widget(['message' => '#Conversion.hint']);
echo '<div class="conversion-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', compact('searchModel'));

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => ['visits_unique', 'visits', 'hits'],
        'template' => '<span class="huge-bold">{visits_unique}</span><br>{visits}<br><small>{hits}</small>',
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
];

if ($user->isAdmin) {
    $columns = array_merge($columns, [
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'template' => '<span class="huge-bold">{wins}</span><br>{manual_wins}<br><small>{tr_total_wins}</small>',
            'attributes' => [
                [
                    'attribute' => 'wins',
                ],
                [
                    'attribute' => 'manual_wins',
                ],
                [
                    'attribute' => 'tr_total_wins',
                ],
            ],
            'hAlign' => 'center',
            'vAlign' => 'middle',
        ],
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'template' => '<span class="huge-bold">{wins_per_visit}</span><br>{mwins_per_visit}<br><small>{twins_per_visit}</small>',
            'format' => ['decimal', 1],
            'attributes' => [
                [
                    'attribute' => 'wins_per_visit',
                ],
                [
                    'attribute' => 'mwins_per_visit',
                ],
                [
                    'attribute' => 'twins_per_visit',
                ],
            ],
            'hAlign' => 'center',
            'vAlign' => 'middle',
        ],
    ]);
}

$columns = array_merge($columns, [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            'queries',
            'queries_unique',
            [
                'attribute' => 'queries_success',
                'label' => Yii::t('app', 'Success'),
            ],
            [
                'attribute' => 'queries_failed',
                'label' => Yii::t('app', 'Fail'),
            ]
        ],
        'template' => '<span class="huge-bold">{queries_unique}</span><br>{queries}<br><small>{queries_success} / {queries_failed}</small>',
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'conversion',
                'format' => 'raw',
                'value' => function (Conversion $m) {
                    return Html::icon('plus') . sprintf('%.02f%%', $m->conversion);
                }
            ],
            [
                'attribute' => 'conversion_success',
                'format' => 'raw',
                'value' => function (Conversion $m) {
                    return Html::icon('plus') . sprintf('%.02f%%', $m->conversion_success);
                }
            ],
            [
                'attribute' => 'record_time',
                'value' => function (Conversion $m) {
                    return DataHelper::durationToText($m->record_time);
                }
            ]
        ],
        'template' => '<span class="huge-bold">{conversion}</span><br><strong>{conversion_success}</strong><br><small>{record_time}</small>',
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
]);

echo $this->render('_conversion_grid', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'options' => [
        'columns' => $columns,
    ],
]);

echo '</div>';
