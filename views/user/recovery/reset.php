<?php

use flyiing\widgets\ActiveForm;

/*
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\RecoveryForm $model
 */

$this->title = Yii::t('user', 'Reset your password');
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin([
    'id'                     => 'password-recovery-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
    'layout' => 'default',
]);
echo $form->field($model, 'email')->textInput([
    'placeholder' => Yii::t('app', 'e-mail'),
    'autofocus' => true,
])->label(false);
echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Reset password'),
        'options' => [
            'class' => 'btn btn-primary btn-block'
        ],
    ],
]);
ActiveForm::end();
