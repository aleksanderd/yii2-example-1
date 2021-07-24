<?php

use flyiing\widgets\AlertFlash;
use app\widgets\ActiveForm;
use kartik\widgets\TouchSpin;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $user app\models\User */

$this->title = Yii::t('app', 'Active tariff dev form');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User tariffs'),
    'url' => ['index', 'user_id' => $user->id],
];
$this->params['breadcrumbs'][] = $this->title;

echo '<div class="user-tariff-dev-active">' . PHP_EOL;

echo AlertFlash::widget();

$form = ActiveForm::begin([
    'id' => 'user-tariff-dev-active-form',
    'enableAjaxValidation' => false,
]);

echo Html::hiddenInput('user_id', $user->id);

echo $form->field($model, 'timeShift')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 7777,
        'postfix' => Yii::t('app', 'days'),
    ],
]);
echo $form->field($model, 'minutes')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 7777,
        'postfix' => Yii::t('app', 'minutes'),
    ],
]);
echo $form->field($model, 'messages')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 7777,
        'postfix' => Yii::t('app', 'messages'),
    ],
]);
echo $form->field($model, 'queries')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 7777,
        'postfix' => Yii::t('app', 'queries'),
    ],
]);
echo $form->field($model, 'space')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 7777,
        'postfix' => Yii::t('app', 'Mb.'),
    ],
]);

echo $form->buttons();
ActiveForm::end();

echo '</div>' . PHP_EOL; // class="user-tariff-view"
