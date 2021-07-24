<?php

use app\models\Variable;
use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use kartik\touchspin\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\Payout */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'payout-form',
    'enableAjaxValidation' => true,
]);

if ($user->isAdmin) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className());
    //echo $form->field($model, 'status')->textInput();
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}

//echo $form->field($model, 'amount')->textInput(['maxlength' => true]);
echo $form->field($model, 'amount')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => Variable::sGet('s.settings.payoutMin', $user->id),
        'max' => Variable::sGet('s.settings.payoutMax', $user->id),
        'decimals' => 0,
        'step' => 1,
        'postfix' => Yii::t('app', 'RUR'),
    ],
]);

echo $form->field($model, 'comment')->textarea(['rows' => 11]);

echo $form->buttons();

ActiveForm::end();
