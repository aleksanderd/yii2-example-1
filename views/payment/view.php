<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Transaction */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Payment') .': #'. $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('payment') . Yii::t('app', 'Payments'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = '#' . $model->id;
//$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#PaymentView.hint']);
echo '<div class="payment-view">' . PHP_EOL;

echo AlertFlash::widget();

$attributes = [
    'id',
    'amount:currency',
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => ViewHelper::paymentStatusSpan($model),
    ],
    'at:datetime',
    'description',
];

if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo '</div>' . PHP_EOL;
