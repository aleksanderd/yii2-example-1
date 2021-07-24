<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TMessage */

$form = ActiveForm::begin([
    'id' => 'client-line-form',
    'enableAjaxValidation' => true,
]);

echo $form->field($model, 'language')->textInput(['readonly' => true, 'maxlength' => 16]);
echo $form->field($model, 'translation')->textarea();
echo $form->buttons();

ActiveForm::end();

