<?php

use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQuery */

$f = Yii::$app->formatter;

$cId = Html::a('#' . $model->id, ['/client-query/view', 'id' => $model->id]);
$cAt = $f->format($model->at, 'time');
if (isset($model->site)) {
    $cFrom = Html::a($model->site->title, ['/client-site/view', 'id' => $model->site_id]);
} else {
    $cFrom = Yii::t('app', 'Unknown site');
}
$cCost = $f->format($model->client_cost, 'currency');

if (($callsCount = $model->getCalls()->count()) > 0) {
    $cCalls = Html::a(Yii::t('app', 'Calls') .': '. $model->getCalls()->count(),
        ['/client-query-call/index', 'ClientQueryCallSearch' => ['query_id' => $model->id]],
        ['target' => '_blank']);
} else {
    $cCalls = '&nbsp;';
}

echo '<tr>';

echo Html::tag('td', $cId, ['class' => 'last-queries-id']);
echo Html::tag('td', $cAt, ['class' => 'last-queries-time']);
echo Html::tag('td', $cFrom, ['class' => 'last-queries-from']);
echo Html::tag('td', $model->call_info, ['class' => 'last-queries-call-info']);
echo Html::tag('td', $this->render('_record_url', compact('model')), ['class' => 'last-queries-record']);
//echo Html::tag('td', $cCost, ['class' => 'last-queries-cost']);
//echo Html::tag('td', $cCalls, ['class' => 'last-queries-calls']);

echo '</tr>';
