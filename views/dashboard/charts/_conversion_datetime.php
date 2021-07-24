<?php

use app\models\ConversionSearch;
use fruppel\googlecharts\GoogleCharts;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\ConversionSearch */
/* @var $options array */

if (!isset($options)) {
    $options = [];
}
$what = ArrayHelper::getValue($options, 'what', 'visits');

$search = clone $searchModel;
if (!$search->isDatetimeGrouped) {
    $search->groupBy = ConversionSearch::GROUP_BY_DT_DAY;
}

$dp = $search->search([]);
/** @var \yii\db\Query $query */
$query = $dp->query;
//echo $query->createCommand()->rawSql;

$cols = [[
    'label' => Yii::t('app', 'Dates'),
    'type' => 'string',
]];
if ($what == 'queries') {
    $visualization = 'SteppedAreaChart';
    //$visualization = 'AreaChart';
    $colors = ['red', 'gray', 'green'];
    $cols[] = [
        'label' => Yii::t('app', 'Unpaid queries'),
        'type' => 'number',
    ];
    $cols[] = [
        'label' => Yii::t('app', 'Failed queries'),
        'type' => 'number',
    ];
    $cols[] = [
        'label' => Yii::t('app', 'Success queries'),
        'type' => 'number',
    ];
} else if ($what == 'conversion') {
    $visualization = 'LineChart';
    $colors = ['blue', 'green'];
    $cols[] = [
        'label' => Yii::t('app', 'Conversion'),
        'type' => 'number',
    ];
    $cols[] = [
        'label' => Yii::t('app', 'Success conversion'),
        'type' => 'number',
    ];
} else if ($what == 'visits_queries') {
    $visualization = 'SteppedAreaChart';
    $colors = ['blue', 'green'];
    $cols[] = [
        'label' => Yii::t('app', 'Unique visits'),
        'type' => 'number',
    ];
    $cols[] = [
        'label' => Yii::t('app', 'Queries'),
        'type' => 'number',
    ];
} else {
    $visualization = 'AreaChart';
    //$visualization = 'LineChart';
    $colors = ['blue', 'gray'];
    $cols[] = [
        'label' => Yii::t('app', 'Unique visits'),
        'type' => 'number',
    ];
    $cols[] = [
        'label' => Yii::t('app', 'Return visits'),
        'type' => 'number',
    ];
}
$data = ['cols' => $cols, 'rows' => []];

$fmt = Yii::$app->formatter;
foreach ($query->all() as $val) {
    /** @var \app\models\Conversion $val */
    $returns = $val->visits - $val->visits_unique;
    if ($search->groupBy === ConversionSearch::GROUP_BY_DT_MONTH) {
        $dt = $fmt->asDate($val->datetime, 'LLLL');
    } else {
        $dt = date('j', $val->datetime);
    }
    $values = [['v' => $dt]];
    if ($what == 'queries') {
        $values[] = ['v' => $val->queries_unpaid];
        $values[] = ['v' => $val->queries_failed];
        $values[] = ['v' => $val->queries_success];
    } else if ($what == 'conversion') {
        $values[] = ['v' => sprintf('%.2f', $val->visits_unique > 0 ? 100 * $val->queries / $val->visits_unique : 0)];
        $values[] = ['v' => sprintf('%.2f', $val->visits_unique > 0 ? 100 * $val->queries_success / $val->visits_unique : 0)];
    } else if ($what == 'visits_queries') {
        $values[] = ['v' => $val->visits_unique];
        $values[] = ['v' => $val->queries];
    } else {
        $values[] = ['v' => $val->visits_unique];
        //$values[] = ['v' => $returns > 0 ? $returns : 0];
        $values[] = ['v' => $val->visits];
    }
    $data['rows'][] = ['c' => $values];
}

echo GoogleCharts::widget([
    'id' => $what . '_chart_datetime_' . $search->hash,
    'responsive' => true,
//    'visualization' => 'AreaChart',
    'visualization' => $visualization,
//    'dataArray' => $data,
    'data' => $data,
    'options' => [
        'chartArea' => [
            'left' => 50,
            'top' => 10,
            'width' => '80%',
            'height' => '85%',
        ],
        'isStacked' => true,
        'lineWidth' => 2,
        //'pointSize' => 2,
        'legend' => [
            'position' => 'in',
        ],
        'vAxis' => [
            'viewWindow' => ['min' => 0],
        ],
        'curveType' => 'function',
        'fontSize' => '10',
        'interpolateNulls' => true,
        'colors' => $colors,
//        'animation' => [
//            'startup' => true,
//            'duration' => 777,
//        ],
    ],
]);


