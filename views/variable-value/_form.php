<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\VariableValue */
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
    'id' => 'variable-value-form',
    'enableAjaxValidation' => true,
]);

echo $this->render('/variable/_select', compact('form', 'model'));
echo $this->render('/user/_select', compact('model', 'form'));

echo $form->field($model, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All websites'),
        'url' => Url::toRoute(['client-site/select-list']),
        'depends' => ['variablevalue-user_id'],
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
]);
echo $form->field($model, 'page_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->page_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All pages'),
        'url' => Url::toRoute(['client-page/select-list']),
        'initDepends' => ['variablevalue-user_id'],
        'depends' => ['variablevalue-site_id'],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
]);
echo $form->field($model, 'value_data')->textarea();

echo $form->buttons();

ActiveForm::end();
