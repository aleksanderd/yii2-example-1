<?php

use app\widgets\SelectUser;
use app\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\UserReferral */

$form = ActiveForm::begin([
    'id' => 'user-referral-form',
    'enableAjaxValidation' => true,
]);

echo $form->field($model, 'partner_id')->widget(SelectUser::className());
echo $form->field($model, 'user_id')->widget(SelectUser::className());
echo $form->field($model, 'scheme')->widget(Select2::className(), [
    'data' => $model->schemeLabels(),
    'hideSearch' => true,
]);
echo $form->field($model, 'status')->widget(Select2::className(), [
    'data' => $model->statusLabels(),
    'hideSearch' => true,
]);
//echo $form->field($model, 'paid')->textInput(['maxlength' => true]);

echo $form->buttons();

ActiveForm::end();
