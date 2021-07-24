<?php

use flyiing\widgets\AlertFlash;
use yii\helpers\Html;
use flyiing\widgets\ActiveForm;
use yii\helpers\Url;

/**
 * @var yii\web\View                   $this
 * @var dektrium\user\models\LoginForm $model
 * @var dektrium\user\Module           $module
 */

$this->title = Yii::t('user', 'Login to GMCF');
$this->params['description'] = Yii::t('app', 'Log in to see it in action.');

echo AlertFlash::widget();

$form = ActiveForm::begin([
    'id'                     => 'login-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
    'validateOnBlur'         => false,
    'validateOnType'         => false,
    'validateOnChange'       => false,
    'layout' => 'default',
]);
echo $form->field($model, 'login', [
    'inputOptions' => [
        'autofocus' => 'autofocus',
        'class' => 'form-control',
        'tabindex' => '1',
    ],
])->textInput(['placeholder' => Yii::t('app', 'Username or e-mail')])->label(false);

echo $form->field($model, 'password', [
    'inputOptions' => [
        'class' => 'form-control',
        'tabindex' => '2',
    ]
])->passwordInput(['placeholder' => Yii::t('user', 'Password')])->label(false);

echo $form->field($model, 'rememberMe')->checkbox(['tabindex' => '4']);

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Log in'),
        'options' => [
            'class' => 'btn btn-primary btn-block'
        ],
    ]
]);

ActiveForm::end();

if ($module->enablePasswordRecovery) {
    echo Html::a(Html::tag('small', Yii::t('user', 'Forgot password?')), ['/user/recovery/request']);
}

if ($module->enableRegistration) {
    echo Html::tag('p', Html::tag('small', Yii::t('app', 'Do not have an account?')), ['class' => 'text-muted text-center']);
    echo Html::a(Yii::t('app', 'Create an account'), Url::to('/user/registration/register'), ['class' => 'btn btn-sm btn-white btn-block']);
}