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
$search->groupBy = ConversionSearch::GROUP_BY_ALL;

$config = [
    'id' => $what . '_chart_pie_' . $search->hash,
    'responsive' => true,
    'visualization' => 'PieChart',
    'options' => [
        'is3D' => true,
        'chartArea' => [
            'width' => '90%',
            'height' => '80%',
        ],
        'pieHole' => 0.4,
        'legend' => 'none',
        'fontSize' => '10',
//        'animation' => [
//            'startup' => true,
//            'duration' => 777,
//        ],
    ],
];

$dp = $search->search([]);
/** @var \yii\db\Query $query */
$query = $dp->query;
//echo $query->createCommand()->rawSql;

$cols = [
    [
        'label' => Yii::t('app', 'Indicator'),
        'type' => 'string',
    ],
    [
        'label' => Yii::t('app', 'Value'),
        'type' => 'number',
    ],
];

/** @var \app\models\Conversion $val */
$val = $query->one();
if ($what == 'queries') {
    $config['options']['colors'] = ['green', 'gray', 'red'];
    $rows = [
        [
            ['v' => Yii::t('app', 'Success queries')],
            ['v' => $val->queries_success],
        ],
        [
            ['v' => Yii::t('app', 'Failed queries')],
            ['v' => $val->queries_failed],
        ],
        [
            ['v' => Yii::t('app', 'Unpaid queries')],
            ['v' => $val->queries_unpaid],
        ],
    ];
} else if ($what == 'conversion') {
    $config['options']['colors'] = ['blue', 'green'];
    $config['options']['pieSliceText'] = 'value';
    $rows = [
        [
            ['v' => Yii::t('app', 'Conversion')],
            ['v' => floatval(sprintf('%.2f', $val->visits_unique > 0 ? 1001 * $val->queries / $val->visits_unique : 0))],
        ],
        [
            ['v' => Yii::t('app', 'Success conversion')],
            ['v' => floatval(sprintf('%.2f', $val->visits_unique > 0 ? 1001 * $val->queries_success / $val->visits_unique : 0))],
        ],
    ];
} else if ($what == 'visits_queries') {
    $config['options']['colors'] = ['blue', 'green'];
    $config['options']['pieSliceText'] = 'value';
    $rows = [
        [
            ['v' => Yii::t('app', 'Unique visits')],
            ['v' => $val->visits_unique],
        ],
        [
            ['v' => Yii::t('app', 'Queries')],
            ['v' => $val->queries],
        ],
    ];
} else {
    $config['options']['colors'] = ['blue', 'gray', 'green'];
    $rows = [
        [
            ['v' => Yii::t('app', 'Unique visits')],
            ['v' => $val->visits_unique],
        ],
        [
            ['v' => Yii::t('app', 'Return visits')],
            ['v' => $val->visits - $val->visits_unique],
        ],
        [
            ['v' => Yii::t('app', 'Queries')],
            ['v' => $val->queries],
        ],
    ];
}
$data = ['cols' => $cols, 'rows' => []];
foreach ($rows as $v) {
    $data['rows'][] = ['c' => $v];
}
$config['data'] = $data;
echo GoogleCharts::widget($config);


