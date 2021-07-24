<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Transaction */
/* @var $form app\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'transaction-form',
//    'enableAjaxValidation' => true,
]);

$allowClear = false;
$placeholder = Yii::t('app', 'Select user');
echo $this->render('/user/_select', compact('model', 'form', 'allowClear', 'placeholder'));
//echo $form->field($model, 'user_id')->textInput();
//echo $form->field($model, 'payment_id')->textInput();
//echo $form->field($model, 'query_id')->textInput();
//echo $form->field($model, 'notification_id')->textInput();
//echo $form->field($model, 'at')->textInput();
echo $form->field($model, 'amount')->textInput(['maxlength' => true]);
echo $form->field($model, 'description')->textInput(['maxlength' => true]);
//echo $form->field($model, 'details_data')->textarea(['rows' => 6]);

echo $form->buttons();

ActiveForm::end();
