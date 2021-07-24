<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TSourceMessage */
/* @var $form flyiing\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'client-line-form',
    'enableAjaxValidation' => true,
]);

echo $form->field($model, 'category')->textInput(['maxlength' => 32]);
echo $form->field($model, 'message')->textarea();
echo $form->buttons();

ActiveForm::end();

