<?php

use app\widgets\SelectNotificationType;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */
/* @var $prefix string */

echo $form->field($model, $prefix)->widget(SelectNotificationType::className());
echo $form->field($model, $prefix . 'EmailSubject')->input('text');
echo $form->field($model, $prefix . 'EmailBody')->textarea([
    'rows' => 11,
]);
echo $form->field($model, $prefix . 'SmsBody')->textarea();
