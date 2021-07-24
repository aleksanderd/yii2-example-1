<?php

use app\widgets\ActiveForm;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $referral \app\models\UserReferral
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'My partner');
$this->params['breadcrumbs'][] = Html::icon('user-referral') . $this->title;

//echo Html::tag('h2', Yii::t('app', 'Partner details'));

echo HintWidget::widget(['message' => '#UserReferralPartner.hint']);
echo '<div class="user-referral-partner">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $referral,
    'attributes' => [
        'partner.username',
        'url.code',
        'url.gift_amount:currency',
    ],
]);

echo AlertFlash::widget();

$form = ActiveForm::begin([
    'id' => 'p_access_form',
]);

echo $form->field($referral, 'p_access')->widget(\kartik\select2\Select2::className(), [
    'data' => $referral->accessLabels(),
    'hideSearch' => true,
]);
echo $form->buttons();

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="user-referral-partner"
