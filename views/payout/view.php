<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use app\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Payout */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Payouts') .': '. $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('payout') . Yii::t('app', 'Payouts'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $model->title;
echo HintWidget::widget(['message' => '#PayoutView.hint']);
echo '<div class="payout-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    'id',
    'amount:currency',
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => ViewHelper::payoutStatusSpan($model),
    ],
    'created_at:datetime',
    'updated_at:datetime',
//    'transaction_id',
    'comment',
];

$actions = UniHelper::getPayoutActions($model, ['update', 'delete', 'retry']);
if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
    if (count($adminActions = UniHelper::getPayoutAdminActions($model)) > 0) {
        $actions = array_merge($adminActions, ['|'], $actions);
    }
}
$this->params['actions'] = $actions;

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo '</div>' . PHP_EOL; // class="payout-view"
