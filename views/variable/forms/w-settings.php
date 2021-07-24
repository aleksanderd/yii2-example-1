<?php

use app\widgets\SelectWidgetAction;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use kartik\touchspin\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Basic widget settings'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';
echo $form->field($model, 'language')->widget(\app\widgets\SelectLanguage::className());

echo $form->field($model, 'style')->widget(Select2::className(), [
    'data' => [
        'default' => Yii::t('app', 'Default style'),
        'cbw-material' => Yii::t('app', 'Material style'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'styleColor')->widget(Select2::className(), [
    'data' => [
        'default' => Yii::t('app', 'Default color'),
        'cbw-light' => Yii::t('app', 'Light color'),
        'cbw-dark' => Yii::t('app', 'Dark color'),
        'cbw-image' => Yii::t('app', 'Image'),
    ],
    'hideSearch' => true,
]);

echo $form->field($model, 'styleDirection')->widget(Select2::className(), [
    'data' => [
        'default' => Yii::t('app', 'Default direction'),
        'cbw-right' => Yii::t('app', 'Right direction'),
    ],
    'hideSearch' => true,
]);

echo $form->field($model, 'btnStyle')->widget(Select2::className(), [
    'data' => [
        'default' => Yii::t('app', 'Default button'),
        'cbb-green' => Yii::t('app', 'Green button'),
        'cbb-blue' => Yii::t('app', 'Blue button'),
        'cbb-black' => Yii::t('app', 'Black button'),
    ],
    'hideSearch' => true,
]);

echo $form->field($model, 'restoreInfo')->widget(Select2::className(), [
    'data' => [
        0 => Yii::t('app', 'Do not restore'),
        1 => Yii::t('app', 'Last call values'),
        2 => Yii::t('app', 'Last valid values'),
        3 => Yii::t('app', 'Last input values'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'defaultPrefix')->widget(\app\widgets\SelectPhonePrefix::className());
echo $form->field($model, 'startDelay')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 9999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);
echo $form->field($model, 'forcedModalDelay')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);
echo $form->field($model, 'intervalMin')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);
echo '</div>'; // panel-body
echo '</div>'; // panel

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Scrolling the page'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';
echo $form->field($model, 'pageEndAction')->widget(SelectWidgetAction::className());
echo $form->field($model, 'pageEndPercent')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 100,
        'boostat' => 5,
        'postfix' => '%',
    ],
]);
echo '</div>'; // panel-body
echo '</div>'; // panel

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Selecting text'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';
echo $form->field($model, 'selectionAction')->widget(SelectWidgetAction::className());
echo $form->field($model, 'selectionMin')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
    ],
]);
echo $form->field($model, 'selectionDelay')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);

echo '</div>'; // panel-body
echo '</div>'; // panel

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Exit catching'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';

echo $form->field($model, 'mouseLeaveAction')->widget(SelectWidgetAction::className());

echo '</div>'; // panel-body
echo '</div>'; // panel

