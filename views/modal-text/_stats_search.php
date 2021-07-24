<?php

use app\helpers\DataHelper;
use app\models\ModalText;
use app\models\ModalTextStatSearch;
use app\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ModalTextStatSearch */

echo '<div class="modal-text-stats-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'method' => 'get',
]);


$texts = ModalText::find()->all();
$items = ArrayHelper::map($texts, 'id', 'title');
echo $form->field($model, 'text_id')->widget(Select2::className(), [
    'data' => $items,
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'No matters'),
    ],
]);

echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All users'),
        'allowClear' => true,
    ],
]);

$items = array_merge(['tr_only' => Yii::t('app', 'Only triggers')], DataHelper::triggersLabels());
echo $form->field($model, 'trigger')->widget(Select2::className(), [
    'data' => $items,
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'No matters'),
    ],
]);

echo $form->field($model, 'groupBy')->widget(Select2::className(), [
    'data' => ModalTextStatSearch::groupByLabels(),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'Do not group'),
    ],
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL;
