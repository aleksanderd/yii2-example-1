<?php

use flyiing\helpers\Html;
use app\models\ClientQuery;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model ClientQuery */

$hours = ArrayHelper::getValue($model->result, 'dHours', '');

if ($model->status >= ClientQuery::STATUS_COMM_SUCCESS) {
    $mod = 'success';
} else if ($model->status >= ClientQuery::STATUS_CLIENT_CONN) {
    $mod = 'info';
} else if ($model->status >= ClientQuery::STATUS_POOL_CONN) {
    $mod = 'warning';
} else if ($model->status == ClientQuery::STATUS_POOL_FAILED || $model->status == ClientQuery::STATUS_UNPAID) {
    $mod = 'danger';
} else {
    $mod = 'default';
}

$content = Html::tag('span', $model->statusLabel, [
    'class' => 'label label-' . $mod,
]);

if (strlen($hours) > 0) {
    $content .= '<br/>' . Html::tag('small', $hours);
}

if (isset($model->deferred_id) && $model->deferred_id > 0) {
    $a = Html::a('#' . $model->deferred_id, ['view', 'id' => $model->deferred_id], [
            'target' => '_blank',
            'data-pjax' => 0,
        ]);
    $content .= '<br/>' . Html::tag('small', Yii::t('app', 'Deferred call') .': '. $a);
}

echo $content;

//echo Html::tag('div', $content, [
//    'class' => 'query-status-label',
//]);

