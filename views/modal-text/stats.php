<?php

use app\helpers\DataHelper;
use app\helpers\ViewHelper;
use app\models\ModalTextStat;
use app\models\ModalTextStatSearch;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ModalTextStatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Modal Texts Statistics');
$this->params['breadcrumbs'][] = Html::icon('modal-text') . $this->title;
$this->params['actions'] = [
    'edit' => [
        'label' => Yii::t('app', 'Edit'),
        'url' => ['/modal-text/index'],
        'options' => ['class' => 'btn-info']
    ],
];

echo '<div class="modal-text-stat-index">' . PHP_EOL;

echo AlertFlash::widget();
echo $this->render('_stats_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'text_id',
        'content' => function (ModalTextStat $m) {
            return $m->text->title;
        }
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'wins',
                'content' => function (ModalTextStat $m) {
                    return ViewHelper::markedInteger($m->wins);
                }
            ],
            [
                'attribute' => 'wins_uni',
                'content' => function (ModalTextStat $m) {
                    return ViewHelper::markedInteger($m->wins_uni);
                }
            ],
        ],
        'hAlign' => 'center',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'queries',
                'content' => function (ModalTextStat $m) {
                    return ViewHelper::markedInteger($m->queries);
                }
            ],
            [
                'attribute' => 'queries_uni',
                'content' => function (ModalTextStat $m) {
                    return ViewHelper::markedInteger($m->queries_uni);
                }
            ],
        ],
        'hAlign' => 'center',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'contentOptions' => ['class' => 'big-bold'],
        'attributes' => [
            [
                'attribute' => 'ctr',
                'format' => ['decimal', 2],
            ],
            [
                'attribute' => 'ctr_uni',
                'format' => ['decimal', 2],
            ],
        ],
        'hAlign' => 'right',
    ],
];

if (!$searchModel->isGrouped || $searchModel->groupBy == ModalTextStatSearch::GROUP_BY_TRIGGER) {
    $columns = array_merge([
        [
            'attribute' => 'trigger',
            'content' => function (ModalTextStat $m) {
                return Yii::t('app', 'tr_' . DataHelper::triggerId($m->trigger, true));
            },
            'hAlign' => 'center',
        ],
    ], $columns);
}

if (!$searchModel->isGrouped || $searchModel->isDatetimeGrouped) {
    $columns = array_merge([
        [
            'attribute' => 'datetime_period',
            'content' => function (ModalTextStat $m) use ($searchModel) {
                $formatter = Yii::$app->formatter;
                if ($searchModel->groupBy == ModalTextStatSearch::GROUP_BY_DT_YEAR) {
                    $content = Html::tag('span', $formatter->asDate($m->datetime_period, 'Y'), [
                        'class' => 'big-bold',
                    ]);
                } else if ($searchModel->groupBy == ModalTextStatSearch::GROUP_BY_DT_MONTH) {
                    $dtStart = strtotime('first hour day', $m->datetime_period);
                    $dtEnd = strtotime('last second', $m->datetime_period);
                    $content = Html::tag('span', $formatter->asDate($m->datetime_period, 'LLLL Y'), [
                        'class' => 'big-bold',
                    ]);
                } else if ($searchModel->groupBy == ModalTextStatSearch::GROUP_BY_DT_DAY) {
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

if (!$searchModel->isGrouped || $searchModel->groupBy == ModalTextStatSearch::GROUP_BY_USER_SITE) {
    $columns = array_merge([['class' => \app\widgets\grid\SiteColumn::className()]], $columns);
}

if (!$searchModel->isGrouped || $searchModel->groupBy == ModalTextStatSearch::GROUP_BY_USER
    || $searchModel->groupBy == ModalTextStatSearch::GROUP_BY_USER_SITE) {

    $columns = array_merge([['class' => \app\widgets\grid\UserColumn::className()]], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="modal-text-index"
