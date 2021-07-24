<?php

use app\models\WidgetHit;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\data\ActiveDataProvider;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\ClientVisit */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'View client visit #{id}', ['id' => $model->id]);
//$this->params['breadcrumbs'][] = ['label' => Html::icon('client-visit') . Yii::t('app', 'Client visits'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;

echo HintWidget::widget(['message' => '#ClientVisitView.hint']);
echo '<div class="client-visit-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    [
        'label' => Yii::t('app', 'Website'),
        'format' => 'raw',
        'value' => $model->site_id ?
            Html::a($model->site->title, ['client-site/view', 'id' => $model->site_id]) : '-',
    ],
    'at:datetime',
    'ip',
    'ref_url:url',
    'user_agent',
];

if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

$hits = $model->getWidgetHits();
$dp = new ActiveDataProvider([
    'query' => $model->getWidgetHits(),
    'sort' => ['defaultOrder' => ['at' => SORT_ASC]],
]);


echo Html::tag('h3', Yii::t('app', 'Visit related hits.'));
echo \app\widgets\grid\GridView::widget([
    'dataProvider' => $dp,
    'columns' => [
        [
            'attribute' => 'id',
            'hAlign' => 'right',
        ],
        [
            'attribute' => 'at',
            'format' => 'datetime',
            'hAlign' => 'center',
        ],
        'url:url',
        [
            'label' => Yii::t('app', 'Call query'),
            'content' => function (WidgetHit $m) {
                $queries = $m->getQueries()->all();
                if (count($queries) < 1) {
                    return '-';
                }
                $res = '';
                foreach ($queries as $q) {
                    $res .= Html::a('#' . $q->id, ['/client-query/view', 'id' => $q->id], [
                        'data-pjax' => 0,
                        'target' => '_blank',
                    ]) . '<br/>';
                }
                return $res;
            },
            'hAlign' => 'center',
        ],
    ],
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-site-view"
