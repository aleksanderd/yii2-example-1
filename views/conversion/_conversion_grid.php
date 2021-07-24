<?php

use flyiing\helpers\Html;
use app\widgets\grid\GridView;
use app\models\Conversion;
use app\models\ConversionSearch;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\ConversionSearch */
/* @var $options array|null */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$columns = ArrayHelper::remove($options, 'columns', []);

/** @var \yii\db\Query $query */
//$query = $dataProvider->query;
//$sql = $query->createCommand()->rawSql;
//echo Html::tag('strong', $sql);

if (!$searchModel->isGrouped || $searchModel->isDatetimeGrouped) {
    $columns = array_merge([
        [
            'attribute' => 'datetime_period',
            'content' => function (Conversion $m) use ($searchModel) {
                $formatter = Yii::$app->formatter;
                if ($searchModel->groupBy == ConversionSearch::GROUP_BY_DT_YEAR) {
                    $content = Html::tag('span', $formatter->asDate($m->datetime_period, 'Y'), [
                        'class' => 'big-bold',
                    ]);
                } else if ($searchModel->groupBy == ConversionSearch::GROUP_BY_DT_MONTH) {
                    $dtStart = strtotime('first hour day', $m->datetime_period);
                    $dtEnd = strtotime('last second', $m->datetime_period);
                    $content = Html::tag('span', $formatter->asDate($m->datetime_period, 'LLLL Y'), [
                        'class' => 'big-bold',
                    ]);
                } else if ($searchModel->groupBy == ConversionSearch::GROUP_BY_DT_DAY) {
                    $dtStart = $dtEnd = strtotime($formatter->asDate($m->datetime_period));
                    $content = Html::tag('span', $formatter->asDate($m->datetime_period, 'd MMMM Y'), [
                        'class' => 'big-bold',
                    ]);
                } else {
                    $date = $formatter->asDate($m->datetime_period);
                    $time = $formatter->asTime($m->datetime_period, 'HH:00') .' - '.
                        $formatter->asTime($m->datetime_period + 3600, 'HH:00');
                    $content = Html::tag('span', $time, ['class' => 'big-bold']) .'<br>'. $date;
                }
                if (false && isset($dtStart, $dtEnd)) {
                    $content .= '|' . $formatter->asDatetime($dtStart) .'|'. $formatter->asDatetime($dtEnd);
                    $sitesUrl = ['',
                        'ConversionSearch' => [
                            'dtStart' => $dtStart,
                            'dtEnd' => $dtEnd,
                            'groupBy' => 'user_id, site_id'
                        ],
                    ];
                    $content .= '<br>' . Html::a(Yii::t('app', 'Websites'), $sitesUrl);
                }
                return $content;
            },
            'hAlign' => 'center',
            'vAlign' => 'middle',
        ],
    ], $columns);
}

$userColumn = [
    'attribute' => 'user_id',
    'label' => Yii::t('app', 'User'),
    'content' => function (Conversion $m) {
        $link = Html::a('#'.$m->user_id, ['/user/admin/update', 'id' => $m->user_id], [
            'target' => '_blank',
            'data-pjax' => 0,
        ]);
        return $link .' '. Html::tag('strong', $m->user->username);
    },
];
if (!$searchModel->isGrouped || $searchModel->groupBy == ConversionSearch::GROUP_BY_USER_SITE) {
    $graphColumn = [
        'label' => Yii::t('app', 'Graph'),
        'content' => function (Conversion $m) {

        }
    ];
    //$columns = array_merge([$graphColumn], $columns);
    $siteColumn = [
        'attribute' => 'site_id',
        'label' => Yii::t('app', 'Website'),
        'content' => function (Conversion $m) {
            $link = Html::a('#'.$m->site_id, ['/client-site/view', 'id' => $m->site_id], [
                'target' => '_blank',
                'data-pjax' => 0,
            ]);
            $url = Html::a($m->site->url, $m->site->url, [
                'target' => '_blank',
                'data-pjax' => 0,
            ]);
            return $link .' '. Html::tag('strong', $m->site->title) .'<br>'. Html::tag('small', $url);
        },
    ];
    if ($user->isAdmin) {
        $columns = array_merge([
            [
                'class' => \flyiing\grid\DataColumn::className(),
                'attributes' => [$userColumn, $siteColumn],
            ]
        ], $columns);
    } else {
        $columns = array_merge([$siteColumn], $columns);
    }
} else {
    if ($user->isAdmin && $searchModel->groupBy == ConversionSearch::GROUP_BY_USER) {
        $columns = array_merge([$userColumn], $columns);
    }
}

echo GridView::widget(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'pjax' => true,
], isset($options) ? $options : []));
