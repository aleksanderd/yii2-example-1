<?php

use app\widgets\SelectWidgetAction;
use app\widgets\slider\IonSlider;

/* @var $this yii\web\View */
/* @var $model app\models\variable\triggers\WTrigger */
/* @var $form app\widgets\ActiveForm */

echo $form->field($model, 'action')->widget(SelectWidgetAction::className());

echo $form->field($model, 'actionDelay')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 60,
        'step' => 1,
        'postfix' => ' ' . Yii::t('app', 's.'),
        'hide_min_max' => true,
        //'hide_from_to' => true,
        'grid' => true,
    ],
]);

echo $form->field($model, 'countLimit')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 20,
        'step' => 1,
        'hide_min_max' => true,
        //'hide_from_to' => true,
        'grid' => true,
    ],
]);

