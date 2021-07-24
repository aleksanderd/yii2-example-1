<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */
/* @var $form app\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'user-tariff-form',
    'enableAjaxValidation' => true,
]);

echo $form->field($model, 'user_id')->textInput();
echo $form->field($model, 'transaction_id')->textInput();
echo $form->field($model, 'tariff_id')->textInput();
echo $form->field($model, 'status')->textInput();
echo $form->field($model, 'renew')->textInput();
echo $form->field($model, 'started_at')->textInput();
echo $form->field($model, 'finished_at')->textInput();
echo $form->field($model, 'lifetime')->textInput();
echo $form->field($model, 'queries')->textInput();
echo $form->field($model, 'queries_used')->textInput();
echo $form->field($model, 'seconds')->textInput();
echo $form->field($model, 'seconds_used')->textInput();
echo $form->field($model, 'messages')->textInput();
echo $form->field($model, 'messages_used')->textInput();
echo $form->field($model, 'space')->textInput();
echo $form->field($model, 'space_used')->textInput();

echo $form->buttons();

ActiveForm::end();
