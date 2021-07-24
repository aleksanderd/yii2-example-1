<?php

use app\widgets\ActiveForm;
use kartik\touchspin\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\Tariff */
/* @var $form app\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'tariff-form',
    'enableAjaxValidation' => true,
]);

echo $form->field($model, 'status')->widget(\kartik\select2\Select2::className(), [
    'data' => \app\models\Tariff::statusLabels(),
    'hideSearch' => true,
]);
echo $this->render('_form_base', compact('form', 'model'));
echo $form->field($model, 'desc_details')->textarea(['rows' => 6]);
echo $form->field($model, 'desc_internal')->textarea(['rows' => 6]);

echo $form->buttons();

ActiveForm::end();
