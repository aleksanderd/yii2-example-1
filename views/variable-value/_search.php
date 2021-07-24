<?php

use app\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\VariableValueSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="variable-value-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $this->render('/user/_select', compact('model', 'form'));
echo $form->field($model, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All websites'),
        'url' => Url::toRoute(['client-site/select-list']),
        'depends' => ['variablevaluesearch-user_id'],
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
        'initDepends' => ['variablevaluesearch-user_id'],
        'depends' => ['variablevaluesearch-site_id'],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="variable-value-search"
