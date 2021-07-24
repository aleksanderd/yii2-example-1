<?php

use app\models\User;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model */
/* @var $form app\widgets\ActiveForm */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

if (!isset($property)) {
    $property = 'user_id';
}
if (!isset($allowClear)) {
    $allowClear = true;
}
if (!isset($placeholder)) {
    $placeholder = Yii::t('app', 'All users');
}
if (count($user->subjectUsers) > 1) {
    echo $form->field($model, $property)->widget(Select2::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'hideSearch' => count($user->subjectUsers) < 7,
        'pluginOptions' => [
            'placeholder' => $placeholder,
            'allowClear' => $user->isAdmin,
        ],
    ])->label(Yii::t('app', 'User'));
} else {
    echo Html::activeHiddenInput($model, $property);
}
