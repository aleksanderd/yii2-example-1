<?php

use app\helpers\ViewHelper;
use app\models\ClientQuery;
use app\models\Conversion;
use app\models\ConversionSearch;
use app\themes\inspinia\widgets\IBoxWidget;
use app\widgets\charts\ConversionPie;
use app\widgets\charts\ConversionTimeline;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $conversionSearch \app\models\ConversionSearch */
/* @var $searchModel \app\models\forms\BasePeriodFilter */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->params['wrapperClass'] = 'gray-bg';
$this->params['pageForm'] = $this->render('_search', compact('searchModel'));

echo HintWidget::widget(['message' => '#DashboardIndex.hint']);
echo AlertFlash::widget();

$cs = clone $conversionSearch;
if ($searchModel->period > 90) {
    $cs->groupBy = ConversionSearch::GROUP_BY_DT_MONTH;
} else if ($searchModel->period > 3) {
    $cs->groupBy = ConversionSearch::GROUP_BY_DT_DAY;
} else {
    $cs->groupBy = ConversionSearch::GROUP_BY_DT_HOUR;
}
$periodLabel = ArrayHelper::getValue($searchModel->periodLabels(), $searchModel->period, '');
$periodSpan = Html::tag('span', $periodLabel, ['class' => 'text-success']);

/** @var \app\models\Conversion $total */
$total = $cs->getModels(['ConversionSearch' => [
    'groupBy' => ConversionSearch::GROUP_BY_ALL,
]])[0];
$ptotal = $cs->getModels(['ConversionSearch' => [
    'groupBy' => ConversionSearch::GROUP_BY_ALL,
]], true)[0];

/**
 * TOTAL
 */

$tpl = ''
    . Html::tag('h1', '{value}')
    . Html::tag('small', '{title}', ['class' => 'stats-label'])
    . Html::tag('div', '{change}');

$totalQueries = IBoxWidget::widget([
    'collapse' => false,
    'title' => Yii::t('app', 'Call queries'),
    'headerAddon' => $periodSpan,
    'content' => ViewHelper::valuesRow([
        strtr($tpl, [
            '{title}' => Yii::t('app', 'Unique queries'),
            '{value}' => $total->queries_unique,
            '{change}' => ViewHelper::changeText($total->queries, $ptotal->queries),
        ]),
        strtr($tpl, [
            '{title}' => Yii::t('app', 'Success queries'),
            '{value}' => $total->queries_success,
            '{change}' => ViewHelper::changeText($total->queries_success, $ptotal->queries_success),
        ]),
    ]),
]);

$totalVisits = IBoxWidget::widget([
    'collapse' => false,
    'title' => Yii::t('app', 'Visits'),
    'headerAddon' => $periodSpan,
    'content' => ViewHelper::valuesRow([
        strtr($tpl, [
            '{title}' => Yii::t('app', 'Total visits'),
            '{value}' => $total->visits,
            '{change}' => ViewHelper::changeText($total->visits, $ptotal->visits),
        ]),
        strtr($tpl, [
            '{title}' => Yii::t('app', 'Unique visits'),
            '{value}' => $total->visits_unique,
            '{change}' => ViewHelper::changeText($total->visits_unique, $ptotal->visits_unique),
        ]),
        strtr($tpl, [
            '{title}' => Yii::t('app', 'Return visits'),
            '{value}' => $total->visits_return,
            '{change}' => ViewHelper::changeText($total->visits_return, $ptotal->visits_return),
        ]),
    ]),
]);

echo '<div class="row">';
echo Html::tag('div', $totalQueries, ['class' => 'col-lg-4 col-md-5']);
echo Html::tag('div', $totalVisits, ['class' => 'col-lg-8 col-md-7']);
echo '</div>';

/**
 * GRAPHS
 */

$visitsGraph = ConversionTimeline::widget([
    'ibox' => false,
    //'summary' => false,
    'type' => ConversionTimeline::TYPE_VISITS,
    'conversionSearch' => $cs,
    'summaryOptions' => ['style' => 'padding-left: 50px'],
]);
$noAxis = [
    'options' => [
        'hAxis' => [
            'gridlines' => ['color' => 'transparent'],
            'textPosition' => 'none',
        ],
        'vAxis' => [
            'gridlines' => ['color' => 'transparent'],
            'textPosition' => 'none',
        ],
        'chartArea' => [
            'left' => 0,
            'height' => '100%',
        ],
    ],
];
$visitsQueriesGraph = ConversionTimeline::widget([
    'ibox' => false,
    'type' => ConversionTimeline::TYPE_VISITS_QUERIES,
    'conversionSearch' => $cs,
    'chartOptions' => $noAxis,
]);
$conversionGraph = ConversionTimeline::widget([
    'ibox' => false,
    'type' => ConversionTimeline::TYPE_CONVERSION,
    'conversionSearch' => $cs,
    'chartOptions' => $noAxis,
]);
$queriesPie = ConversionPie::widget([
    'ibox' => false,
//    'title' => false,
    'type' => ConversionTimeline::TYPE_QUERIES,
    'conversionSearch' => $cs,
]);

echo '<div class="row">';

echo Html::tag('div', $conversionGraph, ['class' => 'col-md-4']);
echo Html::tag('div', $visitsQueriesGraph, ['class' => 'col-md-4']);
echo Html::tag('div', $queriesPie, ['class' => 'col-md-4']);
echo Html::tag('div', '<br>'. $visitsGraph, ['class' => 'col-md-12']);

echo '</div>'; // row
echo '<br>';
echo HintWidget::widget(['message' => '#DashboardIndex.conversionHint']);

/**
 * CONVERSION GRID
 */

$dp = $conversionSearch->search([]);
$dp->pagination->pageSize = 10;
//$dp->sort = false;
$content = $this->render('/conversion/_conversion_grid', [
    'searchModel' => $conversionSearch,
    'dataProvider' => $dp,
    'options' => [
        'summary' => false,
        'columns' => [
            [
                'class' => \flyiing\grid\DataColumn::className(),
                'attributes' => ['visits_unique'],
                'template' => '<span class="huge-bold">{visits_unique}</span>',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
            [
                'class' => \flyiing\grid\DataColumn::className(),
                'attributes' => ['queries', 'queries_unique'],
                'template' => '<span class="huge-bold">{queries_unique}',
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
                ],
                'template' => '<span class="huge-bold">{conversion}</span>',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
        ],
        'bordered' => false,
        'hover' => true,
        'striped' => false,
    ],
]);
$title = Yii::t('app', 'Websites visits, queries and conversion summary');
echo IBoxWidget::widget(compact('content', 'title'));

/**
 * LAST QUERIES
 */

$condition = [];
if ($conversionSearch->user_id > 0) {
    $condition['user_id'] = $conversionSearch->user_id;
}
if ($conversionSearch->site_id > 0) {
    $condition['site_id'] = $conversionSearch->site_id;
}
$lastQueries = ClientQuery::find()
    ->where($condition)
    ->orderBy(['at' => SORT_DESC])
    ->limit(10)
    ->all();

if (count($lastQueries) > 0) {
    echo HintWidget::widget(['message' => '#DashboardIndex.lastQueriesHint']);
    echo $this->render('/client-query/_last', ['queries' => $lastQueries]);
}
