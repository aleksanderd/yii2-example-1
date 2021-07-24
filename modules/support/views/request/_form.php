<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\support\models\SupportForm */

$form = ActiveForm::begin([
    'id' => 'support-form',
    'enableAjaxValidation' => false,
]);

if ($model->user_id === null) {
    echo $form->field($model, 'name');
    echo $form->field($model, 'email');
}

echo $form->field($model, 'subject');
echo $form->field($model, 'message')->textarea(['rows' => 7]);

echo $form->buttons();

ActiveForm::end();

