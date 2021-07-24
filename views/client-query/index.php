<?php

use app\helpers\DataHelper;
use app\models\BlackCallInfo;
use app\models\ClientQuerySearch;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use app\models\ClientQuery;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientQuerySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->registerCssFile('@web/css/query-index.css');

$this->title = Yii::t('app', 'Queries');
$this->params['breadcrumbs'][] = Html::icon('client-query') . $this->title;

if (false && $user->isAdmin) {
    $this->params['actions'] = [
        'queries-admin' => [
            'label' => Yii::t('app', 'Administrate'),
            'url' => ['admin'],
            'options' => ['class' => 'btn-info']
        ],
    ];
}

echo HintWidget::widget(['message' => '#ClientQueryIndex.hint']);
echo '<div class="client-query-index">' . PHP_EOL;
echo AlertFlash::widget();

//echo $this->render('_search', ['model' => $searchModel]);

$sites = \app\models\ClientSite::find();
$sites->andFilterWhere(['user_id' => $searchModel->user_id]);
$sites->andFilterWhere(['user_id' => $user->getSubjectUsers()->select('id')]);

$columns = [
    [
        'class' => \app\widgets\grid\SiteColumn::className(),
        'filterWidgetOptions' => [
            'data' => ArrayHelper::map($sites->all(), 'id', 'title'),
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
//        'filter' => false,
        'filterTemplate' => '{id}',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'attributes' => [
            [
                'attribute' => 'id',
                'content' => function (ClientQuery $m) {
                    return Html::a($m->id, ['view', 'id' => $m->id], ['data-pjax' => 0]);
                }
            ],
            [
                'attribute' => 'call_info_count',
                'label' => Yii::t('app', 'Cnt'),
            ],
        ],
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'at',
        'format' => 'datetime',
        'filter' => false,
        'content' => function(ClientQuery $m) {
            $content = '';
            $content .= Html::tag('span', Yii::$app->formatter->format($m->at, 'time'), ['class' => 'query-time']);
            $content .= '<br>'. Html::tag('span', Yii::$app->formatter->format($m->at, 'date'), ['class' => 'query-date']);
            //$content .= '<br>' . $this->render('_status_label', compact('model'));
            return $content;
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'visit_time',
                'content' => function(ClientQuery $m) {
                    $label = DataHelper::durationToText($m->visit_time);
                    if ($m->visit_id) {
                        return Html::a($label, ['/client-visit/view', 'id' => $m->visit_id], [
                            'target' => '_blank',
                            'data-pjax' => 0,
                        ]);
                    } else {
                        return $label;
                    }
                }
            ],
            [
                'attribute' => 'hit_time',
                'content' => function(ClientQuery $m) {
                    return DataHelper::durationToText($m->hit_time);
                }
            ],
            [
                'attribute' => 'trigger',
                'content' => function(ClientQuery $m) {
                    return Yii::t('app', 'tr_' . DataHelper::triggerId($m->trigger, true));
                }
            ],
        ],
        'hAlign' => 'center',
    ],
//    [
//        'label' => Yii::t('app', 'From'),
//        'content' => function(ClientQuery $m) use ($user) {
//            /** @var \app\models\ClientQuery $m */
//            $content = '';
//            if ($user->isAdmin) {
//                $username = $m->user ? $m->user->username : '?';
//                $link = Html::a(Html::icon('user') . $username, ['/user/admin/update', 'id' => $m->user_id]);
//                $content .= Html::tag('div', $link, ['class' => 'query-user']);
//            }
//            if ($site = $m->site) {
//                $link = Html::a($site->title, ['client-site/view', 'id' => $site->id], ['target' => '_blank']);
//                $content .= Html::tag('div', $link, ['class' => 'query-site']);
//            } else {
//                $content .= Yii::t('app', 'Unknown website');
//            }
//            if (isset($m->url) && strlen($m->url) > 0) {
//                $content .= '<p class="ellipsis url">' . Html::a($m->url, $m->url, ['target' => '_blank']) . '</p>';
//            }
//            return $content;
//        },
//        'contentOptions' => ['class' => 'query-from'],
//        'hAlign' => 'left',
//        'vAlign' => 'middle',
//    ],
    [
        'attribute' => 'call_info',
        'content' => function(ClientQuery $m) use ($user) {
            $content = $m->callInfo;
            $bl = BlackCallInfo::find()
                ->where(['call_info' => $m->call_info])
                ->andWhere(['OR', ['user_id' => $user->id], ['user_id' => null]])
                ->count();
            if ($bl > 0) {
                $content .= '<br>' . Html::tag('span', Yii::t('app', 'blacklisted'), ['class' => 'label label-xs']);
            } else {
                $content .= '<br>' . Html::a(Yii::t('app', 'blacklist it'), [
                        '/black-call-info/create',
                        'call_info' => $m->call_info
                    ], [
                        'target' => '_blank',
                        'class' => 'btn btn-xs',
                        'data-pjax' => 0,
                    ]);
            }
            return $content;
        },
        'contentOptions' => ['class' => 'query-call-info'],
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
//    [
//        'label' => Yii::t('app', 'Rule & Line'),
//        'content' => function($model) {
//            /** @var \app\models\ClientQuery $model */
//            /** @var \app\models\ClientRule $rule */
//            if ($rule = $model->rule) {
//                return Html::a($rule->title, ['/client-rule/view', 'id' => $rule->id], ['target' => '_blank']) .' '.
//                        $this->render('/client-rule/_lines_list', ['lines' => $rule->getLines()]);
//            } else {
//                return Yii::t('app', 'No rule');
//            }
//        },
//        'hAlign' => 'center',
//    ],
    [
        'attribute' => 'status',
        //'filter' => false,
        'filterType' => GridView::FILTER_SELECT2,
        'filterWidgetOptions' => [
            'data' => ClientQuerySearch::statusFilterOptions(),
            'hideSearch' => true,
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'No matters'),
                'allowClear' => true,
            ],
        ],
        'format' => 'raw',
        'content' => function($m) {
            return $this->render('_record_url', ['model' => $m]);
        },
        'hAlign' => 'left',
        'vAlign' => 'top',
    ],
    [
        'label' => Yii::t('app', 'Cost'),
        'attribute' => 'client_cost',
        'content' => function (ClientQuery $m) {
            if ($m->status < ClientQuery::STATUS_POOL_CONN) {
                return '';
            }
            if ($t = $m->userTariff) {
                // TODO
                //$content = Html::a($t->title, ['/user-tariff/view', 'id' => $t->id], ['target' => '_blank']);
                $content = $t->title;
            } else {
                $content = Html::tag('div',
                    Yii::$app->formatter->format($m->client_cost, 'currency'),
                    ['class' => 'cost']);
            }
            $content .= Html::tag('div',
                Html::a(Yii::t('app', 'Calls') .': '. $m->getCalls()->count(),
                    ['/client-query/view', 'id' => $m->id]),
                ['class' => 'calls']);
            return $content;
        },
        'contentOptions' => ['class' => 'query-cost'],
        //'format' => 'currency',
        'vAlign' => 'middle',
        //'hAlign' => 'right',
    ],
];
if (count($user->subjectUsers) > 1) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
            'filterWidgetOptions' => [
                'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
            ],
            //'width' => '20%',
        ]
    ], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'condensed' => true,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-query-index"
