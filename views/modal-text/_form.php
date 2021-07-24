<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ModalText */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'modal-text-form',
    'enableAjaxValidation' => true,
]);

if ($user->isAdmin) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'pluginOptions' => [
            'allowClear' => true,
            'placeholder' => Yii::t('app', 'All users'),
        ],
    ]);
}

echo $form->field($model, 'language')->widget(\app\widgets\SelectLanguage::className());

echo $form->field($model, 'title')->textInput(['maxlength' => true]);
echo $form->field($model, 'm_title')->textInput(['maxlength' => true]);
echo $form->field($model, 'm_submit')->textInput(['maxlength' => true]);
echo $form->field($model, 'm_description')->textarea(['rows' => 11]);

echo $form->buttons();

ActiveForm::end();
