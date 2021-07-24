<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use kartik\color\ColorInput;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\variable\WButtonOptions */
/* @var $form app\widgets\ActiveForm */

echo '<div class="panel-body">';

echo HintWidget::widget(['message' => '#WButtonOptions.hint']) . '<hr/>';

echo $form->field($model, 'position')->widget(Select2::className(), [
    'data' => [
        'lt' => Yii::t('app', 'Top left'),
        'rt' => Yii::t('app', 'Top right'),
        'lb' => Yii::t('app', 'Bottom left'),
        'rb' => Yii::t('app', 'Bottom right'),
    ],
    'hideSearch' => true,
]);

echo $form->field($model, 'margin')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 150,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);

echo $form->field($model, 'animation')->widget(Select2::className(), [
    'data' => ViewHelper::getIOAnimations(),
    'hideSearch' => true,
]);

echo $form->field($model, 'arealSize')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 70,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);

echo $form->field($model, 'radius')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 50,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);

$data = [];
foreach (scandir(Yii::getAlias('@app/web/cli/img/buttons')) as $f) {
    if ($f == '.' || $f == '..') {
        continue;
    }
    $data[$f] = Yii::t('app', $f);
}
echo $form->field($model, 'image')->widget(Select2::className(), [
    'data' => $data,
    'hideSearch' => true,
]);

$colorOpts = [
    'pluginOptions' => [
        'preferredFormat' => 'hex',
    ],
];
echo $form->field($model, 'baseColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'activeColor')->widget(ColorInput::className(), $colorOpts);

echo '</div>';
