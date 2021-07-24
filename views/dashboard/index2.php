<?php

use app\models\ClientQuery;
use app\themes\inspinia\widgets\IBoxWidget;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use flyiing\widgets\AlertFlash;
use app\models\stats\QueriesStats;

/* @var $this yii\web\View */
/* @var $filterModel \app\models\ClientQuerySearch */
/* @var $conversionSearch \app\models\ConversionSearch */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;
$condition = [];
if ($filterModel->user_id > 0) {
    $condition['user_id'] = $filterModel->user_id;
}
if ($filterModel->site_id > 0) {
    $condition['site_id'] = $filterModel->site_id;
}

$this->params['wrapperClass'] = 'gray-bg';

echo HintWidget::widget(['message' => '#DashboardIndex.hint']);
echo AlertFlash::widget();

if ($user->isAdmin) {
    $form = ActiveForm::begin([
        'method' => 'get',
        'id' => 'dashboard-index-filter',
        'enableAjaxValidation' => false,
    ]);
    echo $this->render('/user/_select', [
        'method' => 'get',
        'form' => $form,
        'model' => $filterModel,
    ]);
    echo $form->buttons();
    ActiveForm::end();
}

$sModel = clone $conversionSearch;
$visitsGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel]);
$visitsQueriesGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'visits_queries']]);
$queriesGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);
$visitsPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel]);
$visitsQueriesPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'visits_queries']]);
$queriesPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);

$lastMonthCharts = '';
$tpl = '<div class="row"><div class="col-md-9">%s</div><div class="col-md-3">%s</div></div>';
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days visits for all sites'),
    'content' => sprintf($tpl, $visitsGraph, $visitsPie),
]);
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days visits and queries for all sites'),
    'content' => sprintf($tpl, $visitsQueriesGraph, $visitsQueriesPie),
]);
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days queries for all sites'),
    'content' => sprintf($tpl, $queriesGraph, $queriesPie),
]);
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days conversion for all sites'),
    'content' => sprintf($tpl, $conversionGraph, $conversionPie),
]);

$visitsGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel]);
$queriesGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);
$visitsPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel]);
$queriesPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);

$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days visits top sites'),
    'content' => sprintf($tpl, $visitsGraph, $visitsPie),
]);
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days queries top sites'),
    'content' => sprintf($tpl, $queriesGraph, $queriesPie),
]);
$lastMonthCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Last 30 days conversion top sites'),
    'content' => sprintf($tpl, $conversionGraph, $conversionPie),
]);

/**
 * Last 3 years
 */

$sModel = clone $conversionSearch;
$sModel->dtStart = 0;
$sModel->groupBy = \app\models\ConversionSearch::GROUP_BY_DT_MONTH;
$visitsGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel]);
$visitsQueriesGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'visits_queries']]);
$queriesGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionGraph = $this->render('charts/_conversion_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);
$visitsPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel]);
$visitsQueriesPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'visits_queries']]);
$queriesPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionPie = $this->render('charts/_conversion_pie', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);

$last3YCharts = '';
$tpl = '<div class="row"><div class="col-md-9">%s</div><div class="col-md-3">%s</div></div>';
$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total visits for all sites'),
    'content' => sprintf($tpl, $visitsGraph, $visitsPie),
]);
$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total visits and queries for all sites'),
    'content' => sprintf($tpl, $visitsQueriesGraph, $visitsQueriesPie),
]);
$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total queries for all sites'),
    'content' => sprintf($tpl, $queriesGraph, $queriesPie),
]);

$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total conversion for all sites'),
    'content' => sprintf($tpl, $conversionGraph, $conversionPie),
]);

$visitsGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel]);
$queriesGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionGraph = $this->render('charts/_conversion_sites_datetime', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);
$visitsPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel]);
$queriesPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel, 'options' => ['what' => 'queries']]);
$conversionPie = $this->render('charts/_conversion_sites_pie', ['searchModel' => $sModel, 'options' => ['what' => 'conversion']]);

$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total visits top sites'),
    'content' => sprintf($tpl, $visitsGraph, $visitsPie),
]);
$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total queries top sites'),
    'content' => sprintf($tpl, $queriesGraph, $queriesPie),
]);
$last3YCharts .= IBoxWidget::widget([
    'title' => Yii::t('app', 'Total conversion top sites'),
    'content' => sprintf($tpl, $conversionGraph, $conversionPie),
]);

echo '<div class="row">';
$opts = ['class' => 'col-md-6'];
echo Html::tag('div', $lastMonthCharts, $opts);
echo Html::tag('div', $last3YCharts, $opts);
echo '</div>';

$content = '';
$dp = $conversionSearch->search([]);
$dp->pagination->pageSize = 10;
//$dp->sort = false;
$content .= $this->render('_conversion_grid', [
    'searchModel' => $conversionSearch,
    'dataProvider' => $dp,
    'options' => [
        'summary' => false,
        'bordered' => false,
        'hover' => true,
        'striped' => false,
        'tableOptions' => [
            'class' => 'table1',
        ],
    ],
]);
$title = Yii::t('app', 'Last 30 days stats');
echo IBoxWidget::widget(compact('content', 'title'));

$lastQueries = ClientQuery::find()
    ->where($condition)
    ->orderBy(['at' => SORT_DESC])
    ->limit(10)
    ->all();

if (count($lastQueries) > 0) {
    echo HintWidget::widget(['message' => '#DashboardIndex.lastQueriesHint']);
    echo $this->render('/client-query/_last', ['queries' => $lastQueries]);
}
echo HintWidget::widget(['message' => '#DashboardIndex.periodsHint']);
echo '<div class="row">';

$opts = ['class' => 'col-md-6'];

$last1day = new QueriesStats(['period' => 'last1day', 'condition' => $condition]);
$prev1day = new QueriesStats(['period' => 'prev1day', 'condition' => $condition]);
$title = Yii::t('app', 'Daily statistics');
$content = $this->render('_queries_stats', [
    'last' => $last1day,
    'prev' => $prev1day,
    'labels' => [
        'last' => Yii::t('app', 'Last day'),
        'prev' => Yii::t('app', 'Previous day'),
    ],
]);
$iBox = IBoxWidget::widget(compact('content', 'title'));
echo Html::tag('div', $iBox, $opts);

$last1week = new QueriesStats(['period' => 'last1week', 'condition' => $condition]);
$prev1week = new QueriesStats(['period' => 'prev1week', 'condition' => $condition]);
$title = Yii::t('app', 'Weekly statistics');
$content = $this->render('_queries_stats', [
    'last' => $last1week,
    'prev' => $prev1week,
    'labels' => [
        'last' => Yii::t('app', 'Last week'),
        'prev' => Yii::t('app', 'Previous day'),
    ],
]);
$iBox = IBoxWidget::widget(compact('content', 'title'));
echo Html::tag('div', $iBox, $opts);

$last1month = new QueriesStats(['period' => 'last1month', 'condition' => $condition]);
$prev1month = new QueriesStats(['period' => 'prev1month', 'condition' => $condition]);
$title = Yii::t('app', 'Monthly statistics');
$content = $this->render('_queries_stats', [
    'last' => $last1month,
    'prev' => $prev1month,
    'labels' => [
        'last' => Yii::t('app', 'Last month'),
        'prev' => Yii::t('app', 'Previous month'),
    ],
]);
$iBox = IBoxWidget::widget(compact('content', 'title'));
echo Html::tag('div', $iBox, $opts);

$last1year = new QueriesStats(['period' => 'last1year', 'condition' => $condition]);
$prev1year = new QueriesStats(['period' => 'prev1year', 'condition' => $condition]);
$title = Yii::t('app', 'Yearly statistics');
$content = $this->render('_queries_stats', [
    'last' => $last1year,
    'prev' => $prev1year,
    'labels' => [
        'last' => Yii::t('app', 'Last year'),
        'prev' => Yii::t('app', 'Previous year'),
    ],
]);
$iBox = IBoxWidget::widget(compact('content', 'title'));
echo Html::tag('div', $iBox, $opts);

echo '</div>'; // class=row
