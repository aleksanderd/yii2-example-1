<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BlackCallInfo */

$form = ActiveForm::begin([
    'id' => 'black-call-info-form',
    'enableAjaxValidation' => true,
]);

echo $this->render('/user/_select', compact('form', 'model'));
echo $form->field($model, 'call_info')->textInput(['maxlength' => true]);
echo $form->field($model, 'comment')->textarea(['rows' => 6]);

echo $form->buttons();

ActiveForm::end();
