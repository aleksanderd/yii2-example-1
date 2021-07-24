<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Variable */
/* @var $form app\widgets\ActiveForm */

$submitOptions = ['type' => 'submit', 'class' => 'btn'];
if ($model->isNewRecord) {
    $submitLabel = Yii::t('app', 'Add');
    Html::addCssClass($submitOptions, 'btn-success');
} else {
    $submitLabel = Yii::t('app', 'Save');
    Html::addCssClass($submitOptions, 'btn-primary');
}

$form = ActiveForm::begin([
    'id' => 'variable-form',
    'enableAjaxValidation' => true,
]);

echo $this->render('/user/_select', compact('model', 'form'));
// TODO выбор типа
Html::activeHiddenInput($model, 'type_id');
echo $form->field($model, 'name')->textInput(['maxlength' => true]);

echo $form->buttons();

ActiveForm::end();
