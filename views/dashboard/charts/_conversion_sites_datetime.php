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

$sites = clone $searchModel;
$sites->groupBy = ConversionSearch::GROUP_BY_USER_SITE;
$dp = $sites->search([]);
/** @var \yii\db\Query $query */
$query = $dp->query;
$sites = $query->orderBy(['queries' => SORT_DESC])->limit(10)->all();
$cols = [[
    'label' => Yii::t('app', 'Dates'),
    'type' => 'string',
]];
$dts = [];
$fmt = Yii::$app->formatter;
foreach ($sites as $s) {
    /** @var \app\models\Conversion $s */
    if (!($site = \app\models\ClientSite::findOne($s->site_id))) {
        continue;
    }
    /** @var \app\models\ClientSite $site */
    $cols[] = [
        'label' => $site->title,
        'type' => 'number',
    ];
    $search = clone $searchModel;
    $search->site_id = $site->id;
    if (!$search->isDatetimeGrouped) {
        $search->groupBy = ConversionSearch::GROUP_BY_DT_DAY;
    }
    $dp = $search->search([]);
    /** @var \yii\db\Query $query */
    $query = $dp->query;
    foreach ($query->all() as $v) {
        /** @var \app\models\Conversion $v */
        if ($what == 'queries') {
            $value = $v->queries;
        } else if ($what == 'conversion') {
            $value = sprintf('%.2f', $v->visits_unique > 0 ? 100 * $v->queries / $v->visits_unique : 0);
        } else {
            $value = $v->visits_unique;
        }
        if ($searchModel->groupBy === ConversionSearch::GROUP_BY_DT_MONTH) {
            $dt = date('Y-m-01', $v->datetime);
        } else {
            $dt = date('Y-m-d', $v->datetime);
        }
        $dts[$dt][$s->site_id] = $value > 0 ? $value : 0;
    }
}
ksort($dts);
$rows = [];
foreach ($dts as $dt => $dtSites) {
    $v = strtotime($dt);
    if ($searchModel->groupBy === ConversionSearch::GROUP_BY_DT_MONTH) {
        $v = $fmt->asDate($v, 'LLLL');
    } else {
        $v = $fmt->asDate($v, 'd');
    }
    $values = [['v' => $v]];
    foreach ($sites as $s) {
        /** @var \app\models\Conversion $s */
        $values[] = ['v' => ArrayHelper::getValue($dtSites, $s->site_id, 0)];
    }
    $rows[] = ['c' => $values];
}

$data = ['cols' => $cols, 'rows' => $rows];
//echo count($dts);
//echo Html::tag('pre', print_r($dts, true));
//echo Html::tag('pre', print_r($data, true));

echo GoogleCharts::widget([
    'id' => $what . '_chart_sites_datetime_' . $search->hash,
    'responsive' => true,
    'visualization' => 'LineChart',
//    'dataArray' => $data,
    'data' => $data,
    'options' => [
        'chartArea' => [
            'left' => 50,
            'top' => 10,
            'width' => '96%',
            'height' => '85%',
        ],
        //'isStacked' => true,
        'lineWidth' => 2,
        //'pointSize' => 3,
        'legend' => [
            'position' => 'in',
            'maxLines' => 3,
        ],
        'vAxis' => [
            'viewWindow' => ['min' => 0],
        ],
        'curveType' => 'function',
        'fontSize' => '10',
        //'interpolateNulls' => true,
        //'colors' => $colors,
//        'animation' => [
//            'startup' => true,
//            'duration' => 777,
//        ],
    ],
]);


