<?php

use app\models\STicket;
use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\STicket */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'sticket-form',
    'layout' => 'default',
    'enableAjaxValidation' => true,
]);

echo Html::activeHiddenInput($model, 'user_id');

echo $form->field($model, 'site_id')->widget(Select2::className(), [
    'data' => ArrayHelper::map($user->clientSites, 'id', 'title'),
    'hideSearch' => true,
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'No matters'),
        'allowClear' => true,
    ],
]);

echo $form->field($model, 'topic_id')->widget(Select2::className(), [
    'data' => STicket::topicLabels(),
    'hideSearch' => true,
]);

echo $form->field($model, 'title')->textInput(['maxlength' => true]);

echo $this->render('_message_input', compact('form', 'model'));

echo $form->buttons();

ActiveForm::end();
