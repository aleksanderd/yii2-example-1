<?php

use app\widgets\ActiveForm;
use yii\helpers\Inflector;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $keyModel yii\base\DynamicModel */
/* @var $name string */

$viewPath = __DIR__ . '/forms/' . $name . '.php';
if (!is_readable($viewPath)) {
    echo $viewPath . ': form view not found.';
}

$form = ActiveForm::begin([
    //'fieldClass' => \app\widgets\VariableField::className(),
    'id' => Inflector::camel2id($model->formName() . 'Form', '-', true),
    'enableAjaxValidation' => false,
]);

echo $this->render('/user/_select', ['model' => $keyModel, 'form' => $form]);
echo $form->field($keyModel, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All websites'),
        'url' => Url::toRoute(['client-site/select-list']),
        'depends' => [Html::getInputId($keyModel, 'user_id')],
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
]);
echo $form->field($keyModel, 'page_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->page_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All pages'),
        'url' => Url::toRoute(['client-page/select-list']),
        'initDepends' => [Html::getInputId($keyModel, 'user_id')],
        'depends' => [Html::getInputId($keyModel, 'site_id')],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
]);

if ($model->user_id !== null && $model->user_id > 0) {
    $form->fieldClass = \app\widgets\VariableField::className();
}

echo Html::hiddenInput('_action', 'save', ['id' => '_action']);
echo $form->buttons([
    'load' => [
        'label' => Yii::t('app', 'Load values'),
        'options' => [
            'type' => 'submit',
            'class' => 'btn btn-info',
            'onclick' => new JsExpression('$("#_action").val("load")'),
//            'data-pjax' => 1,
        ],
    ],
    //'submit' => ['label' => Yii::t('app', 'Save')],
]);

Pjax::begin([
    'id' => 'pjax-widget',
    'formSelector' => '#variable-form',
    'linkSelector' => false,
]);
echo AlertFlash::widget();

echo Html::activeHiddenInput($model, 'user_id');
echo Html::activeHiddenInput($model, 'site_id');
echo Html::activeHiddenInput($model, 'page_id');

echo $this->render('forms/' . $name, compact('model', 'form'));

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Save values'),
        'options' => [
            'onclick' => new JsExpression('$("#_action").val("save")'),
        ],
    ],
    //'reset',
]);
Pjax::end();

ActiveForm::end();
