<?php

use app\widgets\ActiveForm;
use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ClientLine */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'client-line-form',
    'enableAjaxValidation' => true,
]);

if ($model->isNewRecord && count($user->subjectUsers) > 1) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'hideSearch' => true,
    ])->label(Yii::t('app', 'User'));
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}

//echo $form->field($model, 'type_id')->hiddenInput();
echo $form->field($model, 'title')->textInput(['maxlength' => 70]);
echo $form->field($model, 'description')->textInput(['maxlength' => 255]);
echo $form->field($model, 'info')->textInput(['maxlength' => 255]);
echo $form->buttons();

ActiveForm::end();

