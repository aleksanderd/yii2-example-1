<?php

use app\widgets\rl\RuleLineWidget;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */
/* @var $form app\widgets\ActiveForm */

echo $form->field($model, 'linesIDs')->widget(RuleLineWidget::className(), [
    'url' => ['client-line/select-list'],
]);

/*
echo $form->field($model, 'line_id')->widget(\kartik\depdrop\DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->line_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'Select line'),
        'url' => Url::toRoute(['client-line/select-list']),
        'initDepends' => [Html::getInputId($model, 'user_id')],
        'depends' => [Html::getInputId($model, 'user_id')],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
//            'allowClear' => true,
        ],
    ],
]);
*/