<?php

use flyiing\widgets\ActiveForm;
use yii\helpers\Url;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'action' => Url::toRoute("/user/security/login", true),
    'options' => [
        'target' => "_blank",
    ]
]);

echo $form->field($model, 'login')
    ->textInput(['placeholder' => Yii::t('app', 'Username or e-mail')])->label(false);

echo $form->field($model, 'password')
    ->passwordInput(['placeholder' => Yii::t('user', 'Password')])->label(false);

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Log in'),
        'options' => [
            'class' => 'btn btn-primary btn-block'
        ],
    ]
]);

ActiveForm::end();
//echo Html::tag('p', Html::tag('small', Yii::t('app', 'Do not have an account?')), ['class' => 'text-muted text-center']);
//echo Html::a(Yii::t('app', 'Create an account'), Url::toRoute("user/registration/register", true), ['class' => 'btn btn-sm btn-white btn-block']);
