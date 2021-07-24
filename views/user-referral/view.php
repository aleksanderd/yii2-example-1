<?php

use app\helpers\ViewHelper;
use app\models\Payment;
use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserReferral */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$title = $user->isAdmin ? '['. $model->partner->username . '] ' : '';
$title .= $model->user->username;
$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('user-referral') . Yii::t('app', 'User Referrals'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $title;
if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);
}

echo HintWidget::widget(['message' => '#UserReferralView.hint']);
echo '<div class="user-referral-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    'user.username',
    'user.created_at:datetime',
    'schemeText',
    [
        'attribute' => 'scheme',
        'format' => 'raw',
        'value' => $model->isActive ? $model->schemeTextPct : ViewHelper::UserReferralScheme($model),
    ],
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => ViewHelper::userReferralStatusSpan($model),
    ],
    'referralPaid:currency',
    'paid:currency',
];

if ($user->isAdmin) {
    $attributes = array_merge(['partner.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo Html::tag('h3', Yii::t('app', 'Partner income'));
echo HintWidget::widget(['message' => '#UserReferralViewLog.hint']);

$dp = new \yii\data\ActiveDataProvider(['query' => $model->getTransactions()->orderBy(['at' => SORT_DESC])]);
echo \kartik\grid\GridView::widget([
    'dataProvider' => $dp,
    'columns' => [
        'at:datetime',
        'payment.amount:currency',
        'transaction.amount:currency',
    ],
]);

echo Html::tag('h3', Yii::t('app', 'Referral payments'));
echo HintWidget::widget(['message' => '#UserReferralViewPaymentLog.hint']);

$q = $model->user->getPayments()
    ->where(['status' => Payment::STATUS_COMPLETED])
    ->orderBy(['at' => SORT_DESC]);
$dp = new \yii\data\ActiveDataProvider(['query' => $q]);
echo \kartik\grid\GridView::widget([
    'dataProvider' => $dp,
    'columns' => [
        'at:datetime',
        'amount:currency',
    ],
]);

echo '</div>' . PHP_EOL; // class="user-referral-view"
