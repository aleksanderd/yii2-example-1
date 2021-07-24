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
$search->groupBy = ConversionSearch::GROUP_BY_USER_SITE;

$config = [
    'id' => $what . '_chart_site_pie_' . $search->hash,
    'responsive' => true,
    'visualization' => 'PieChart',
    'options' => [
        //'is3D' => true,
        'chartArea' => [
            'width' => '90%',
            'height' => '80%',
        ],
        'pieHole' => 0.4,
        'legend' => 'none',
        'fontSize' => '10',
//        'colors' => $colors,
//        'animation' => [
//            'startup' => true,
//            'duration' => 777,
//        ],
    ],
];

$dp = $search->search([]);
/** @var \yii\db\Query $query */
$query = $dp->query;

if ($what == 'queries') {
    $query->orderBy(['queries' => SORT_DESC]);
    $label = Yii::t('app', 'Queries');
} else if ($what == 'conversion') {
    $query->orderBy(['IF(`visits` > 11, 100*`queries`/`visits_unique`, 0)' => SORT_DESC]);
    $label = Yii::t('app', 'Conversion');
    $config['options']['pieSliceText'] = 'value';
} else {
    $query->orderBy(['visits_unique' => SORT_DESC]);
    $label = Yii::t('app', 'Unique visits');
}
$search = $query->limit(10)->all();

$cols = [
    [
        'label' => Yii::t('app', 'Website'),
        'type' => 'string',
    ],
    [
        'label' => $label,
        'type' => 'number',
    ],
];

$vals = $query->all();
foreach ($vals as $val) {
    /** @var \app\models\Conversion $val */
    if (!($site = \app\models\ClientSite::findOne($val->site_id))) {
        continue;
    }
    if ($what == 'queries') {
        $value = $val->queries;
    } else if ($what == 'conversion') {
        $value = floatval(sprintf('%.2f', $val->visits_unique > 0 ? 100 * $val->queries / $val->visits_unique : 0));
    } else {
        $value = $val->visits_unique;
    }
    /** @var \app\models\ClientSite $site */
    $rows[] = ['c' => [
        ['v' => $site->title],
        ['v' => $value],
    ]];
}

$config['data'] = ['cols' => $cols, 'rows' => $rows];
echo GoogleCharts::widget($config);


