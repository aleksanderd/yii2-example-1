<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PaymentSearch */

echo '<div class="payment-stats-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'id' => 'payment-stats-search-form',
    'action' => ['stats'],
    'method' => 'get',
]);

echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All users'),
        'allowClear' => true,
    ],
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL;
