<?php

use yii\helpers\Html;
use flyiing\widgets\ActiveForm;
use yii\helpers\Url;

/*
 * @var yii\web\View $this
 * @var dektrium\user\models\RecoveryForm $model
 */

$this->title = Yii::t('user', 'Recover your password');
$this->params['description'] = 'Enter your email address and your password will be reset and emailed to you. ';

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
        'label' => Yii::t('app', 'Send new password'),
        'options' => [
            'class' => 'btn btn-primary btn-block'
        ],
    ],
]);
ActiveForm::end();

echo Html::tag('p', Html::tag('small', Yii::t('app', 'Already have the password?')), ['class' => 'text-muted text-center']);
echo Html::a(Yii::t('app', 'Login'), Url::to('/user/security/login'), ['class' => 'btn btn-sm btn-white btn-block']);
echo '<br />';
echo Html::tag('p', Html::tag('small', Yii::t('app', 'Do not have an account?')), ['class' => 'text-muted text-center']);
echo Html::a(Yii::t('app', 'Create an account'), Url::to('/user/registration/register'), ['class' => 'btn btn-sm btn-white btn-block']);
