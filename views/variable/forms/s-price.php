<?php

use flyiing\helpers\Html;
use kartik\touchspin\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Prices'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';
//echo $form->field($model, 'callMinute')->input('text');
$opts = [
    'min' => 0,
    'decimals' => 2,
    'step' => 0.01,
    'postfix' => Yii::t('app', 'RUR'),
];
echo $form->field($model, 'callMinute')->widget(TouchSpin::className(), ['pluginOptions' => $opts]);
echo $form->field($model, 'sms')->widget(TouchSpin::className(), ['pluginOptions' => $opts]);
echo $form->field($model, 'email')->widget(TouchSpin::className(), ['pluginOptions' => $opts]);
echo '</div>'; // panel-body
echo '</div>'; // panel

