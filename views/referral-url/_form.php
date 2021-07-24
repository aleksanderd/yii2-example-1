<?php

use app\models\ReferralUrl;
use app\models\Variable;
use app\widgets\ActiveForm;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\ReferralUrl */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'referral-url-form',
    'enableAjaxValidation' => true,
]);

if ($user->isAdmin) {
    echo $form->field($model, 'status')->widget(Select2::className(), [
        'data' => ReferralUrl::statusLabels(),
        'hideSearch' => true,
    ]);
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className());
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}
echo $form->field($model, 'title')->textInput(['maxlength' => true]);

$moneyOpts = [
    'pluginOptions' => [
        'min' => 0,
        'max' => Variable::sGet('s.settings.referralGiftMax', $model->user_id),
        'decimals' => 0,
        'step' => 1,
        'postfix' => Yii::t('app', 'RUR'),
    ],
];
echo $form->field($model, 'gift_amount')->widget(TouchSpin::className(), $moneyOpts);

/*
$promocodePlaceholder = Yii::t('app', 'Without promocode');
echo $form->field($model, 'promocode_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->promocode_id => $model->promocode ? $model->promocode->code : $promocodePlaceholder],
    'pluginOptions' => [
        'placeholder' => $promocodePlaceholder,
        'url' => Url::toRoute('promocode/select-list'),
        'depends' => ['referralurl-user_id'],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
])->label(Yii::t('app', 'Promocode'));
*/
echo $form->buttons();

ActiveForm::end();
