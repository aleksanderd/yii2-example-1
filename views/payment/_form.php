<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\Payment;

/* @var $this yii\web\View */
/* @var $model app\models\Payment */
/* @var $form app\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'payment-form',
    'enableAjaxValidation' => true,
]);

$allowClear = false;
$placeholder = Yii::t('app', 'Select user');
echo $this->render('/user/_select', compact('model', 'form', 'allowClear', 'placeholder'));

echo $form->field($model, 'status')->widget(Select2::className(), [
    'data' => Payment::statusLabels(),
    'hideSearch' => true,
]);

echo Html::activeHiddenInput($model, 'method');
echo $form->field($model, 'amount')->widget(\kartik\money\MaskMoney::className());
echo $form->field($model, 'description')->textInput(['maxlength' => true]);

echo $form->buttons();
ActiveForm::end();

