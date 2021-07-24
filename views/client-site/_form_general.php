<?php

/* @var $model app\models\ClientSite */
/* @var $form app\widgets\ActiveForm */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

if ($model->isNewRecord && count($user->subjectUsers) > 1) {
    echo $form->field($model, 'user_id')->widget('kartik\select2\Select2', [
        'data' => \yii\helpers\ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'hideSearch' => count($user->subjectUsers) < 7,
    ])->label(Yii::t('app', 'User'));
}
echo $form->field($model, 'title')->textInput(['maxlength' => 70]);
echo $form->field($model, 'description')->textInput(['maxlength' => 255]);
echo $form->field($model, 'url')->textInput(['maxlength' => 255]);
echo $form->field($model, 'defaultPrefix')->widget(\app\widgets\SelectPhonePrefix::className());

