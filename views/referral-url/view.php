<?php

use app\helpers\ViewHelper;
use app\models\UserReferral;
use app\widgets\hint\HintWidget;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ReferralUrl */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->titleText;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('referral-url') . Yii::t('app', 'Referral urls'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;


$attributes = [
    'id',
    'titleText',
    'code',
    'url:url',
    'gift_amount:currency',
    'statusText',
    'created_at:datetime',
    'stats.visits',
    'stats.registered',
    'stats.active',
    'stats.paid:currency',
    'stats.gifts_activated',
    'stats.gifts_paid:currency',
];

$actions = ['delete'];
if ($user->isAdmin) {
    array_unshift($actions, 'update');
    array_unshift($attributes, 'user.username');
}
if (!$model->isDefault) {
    $this->params['actions'] = UniHelper::getModelActions($model, $actions);
}

echo HintWidget::widget(['message' => '#ReferralUrlView.hint']);
echo '<div class="referral-url-view">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo Html::tag('h3', Yii::t('app', 'Referral users'));
echo HintWidget::widget(['message' => '#ReferralUrlViewReferrals.hint']);

$dp = new ActiveDataProvider(['query' => $model->getUserReferrals()]);
echo GridView::widget([
    'dataProvider' => $dp,
    'columns' => [
        [
            'attribute' => 'user.username',
            'content' => function (UserReferral $m) {
                return Html::a($m->user->username, Url::to([
                    '/user-referral/view',
                    'partner_id' => $m->partner_id,
                    'user_id' => $m->user_id
                ]));
            },
        ],
        [
            'attribute' => 'status',
            'content' => function (UserReferral $m) {
                return ViewHelper::userReferralStatusSpan($m);
            },
            'hAlign' => 'center',
        ],
        [
            'attribute' => 'scheme',
            'content' => function (UserReferral $m) {
                return ViewHelper::UserReferralScheme($m);
            },
            'hAlign' => 'center',
        ],
        [
            'attribute' => 'user.created_at',
            'format' => 'datetime',
            'hAlign' => 'center',
        ],
        [
            'attribute' => 'paid',
            'format' => 'currency',
            'hAlign' => 'right',
        ],
    ],
]);

echo '</div>' . PHP_EOL; // class="referral-url-view"
