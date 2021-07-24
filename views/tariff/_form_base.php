<?php

use kartik\touchspin\TouchSpin;
use kartik\select2\Select2;
use app\models\Tariff;

/* @var $this yii\web\View */
/* @var $model app\models\Tariff */
/* @var $form app\widgets\ActiveForm */

echo $form->field($model, 'title')->textInput(['maxlength' => true]);
echo $form->field($model, 'desc')->textInput(['maxlength' => true]);
echo $form->field($model, 'renewable')->widget(\kartik\checkbox\CheckboxX::className(), [
    'pluginOptions' => [
        'threeState' => false,
    ],
]);
echo $form->field($model, 'price')->widget(\kartik\money\MaskMoney::className(), []);

$tOpts = [
    'pluginOptions' => [
        'min' => 0,
        'max' => 777777,
    ],
];
echo $form->field($model, 'lifetime_measure')->widget(Select2::className(), [
    'data' => Tariff::ltmLabels(),
    'hideSearch' => true,
]);
echo $form->field($model, 'lifetime')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 777777,
        'postfix' => Yii::t('app', 'days'),
    ],
]);
echo $form->field($model, 'queries')->widget(TouchSpin::className(), $tOpts);
echo $form->field($model, 'minutes')->widget(TouchSpin::className(), $tOpts);
echo $form->field($model, 'messages')->widget(TouchSpin::className(), $tOpts);
echo $form->field($model, 'space')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 777777,
        'postfix' => Yii::t('app', 'Mb.'),
    ],
]);
