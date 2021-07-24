<?php

use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */

$this->title = $model->id .': '. $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User tariffs'),
    'url' => ['index', 'user_id' => $model->user_id],
];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#UserTariffView.hint']);
echo '<div class="user-tariff-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user_id',
        'tariff_id',
        'status',
        'renew',
        'started_at:datetime',
        'finished_at:datetime',
        'lifetime:datetime',
        'queries',
        'queries_used',
        'seconds',
        'seconds_used',
        'messages',
        'messages_used',
        'space',
        'space_used',
    ],
]);

echo '</div>' . PHP_EOL; // class="user-tariff-view"
