<?php

use app\models\ClientSite;
use dosamigos\chartjs\ChartJs;
use flyiing\helpers\Html;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $condition array */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$user_id = ArrayHelper::remove($condition, 'user_id', 0);
$sites = new Query();
$sites->select([
    'site_id' => 'site_id',
    'visits' => 'COUNT(*)',
])->from('{{%client_visit}}');
$sites->where($condition)
    ->groupBy('site_id')
    ->orderBy(['visits' => SORT_DESC])
    ->limit(10);
//$sql = $sites->createCommand()->rawSql;
//echo $sql;
$sites = $sites->all();

$data = [];
foreach ($sites as $s) {
    $query = new Query();

    $select = [
        'dt' => 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-%d 00:00:00"))',
        'visits' => 'COUNT(*)',
    ];
    $from = '';
    $query->select($select)
        ->from('{{%client_visit}}')
        ->where($condition)
        ->groupBy('dt')
        ->orderBy(['dt' => SORT_ASC]);
    $query->andWhere(['site_id' => $s['site_id']]);

    $sql = $query->createCommand()->rawSql;
    //echo $sql . '<hr/>';
    $values = $query->all();
    foreach ($values as $v) {
        $label = date('m.d', $v['dt']);
        $data[$label][$s['site_id']] = [
            'visits' => $v['visits'],
        ];
    }
}
ksort($data);
//echo Html::tag('pre', print_r($data, true));

$colors = [
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'green',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'blue',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'purple',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'dark-blue',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'navy',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'maroon',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'orange',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'teal',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'indigo',
    ],
    [
        'fillColor' => 'transparent',
        'strokeColor' => 'yellowgreen',
    ],
];
$ci = 0;
$cl = count($colors);
$datasets = [];
foreach ($sites as $s) {
    if (!($site = ClientSite::findOne($s['site_id']))) {
        continue;
    }
    /** @var \app\models\ClientSite $site */
    $sdata = [];
    foreach ($data as $k => $v) {
        $sdata[] = ArrayHelper::getValue($data, $k .'.'. $s['site_id'] .'.visits', 0);
    }
    $color = $colors[$ci++];
    if ($ci >= $cl) {
        $ci = 0;
    }
    $datasets[] = array_merge($color, [
        'label' => $site->title,
        'data' => $sdata,
    ]);
}

$config = [
    'type' => 'Line',
    'options' => [
        'style' => 'padding-right: 50px; max1-height: 200px',
        //'max-height' => '200px',
        //'width' => '100%',
    ],
    'data' => [
        'labels' => array_keys($data),
        'datasets' => $datasets,
    ],
    'clientOptions' => [
        'animation' => false,
        //'maintainAspectRatio' => false,
        'scaleShowVerticalLines' => false,
        'datasetStrokeWidth' => 1,
        'responsive' => true,
        'datasetFill' => false,
        'pointDotRadius' => 2,
        //'bezierCurve' => false,
    ],
];

//echo Html::tag('pre', print_r($config, true));

echo ChartJs::widget($config);

