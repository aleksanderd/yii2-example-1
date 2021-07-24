<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;
use yii\helpers\Url;

$form = ActiveForm::begin([
    'id' => 'registration-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'action' => Url::toRoute("/user/registration/register", true),
    'options' => [
        'target' => "_blank",
    ],
    'layout' => 'default',
]);

echo $form->field($model, 'website')
    ->textInput(['placeholder' => Yii::t('app', 'Your website')])
    ->label(false);

echo $form->field($model, 'name')
    ->textInput(['placeholder' => Yii::t('app', 'Your name')])
    ->label(false);

echo $form->field($model, 'phone')
    ->textInput(['placeholder' => Yii::t('app', 'Phone number')])
    ->label(false);

echo $form->field($model, 'email')
    ->textInput(['placeholder' => Yii::t('app', 'e-mail')])->label(false);

echo $form->field($model, 'username')
    ->textInput(['placeholder' => Yii::t('app', 'Username')])->label(false);

echo $form->field($model, 'password')
    ->passwordInput(['placeholder' => Yii::t('app', 'Password')])->label(false);

echo $form->field($model, 'referral')
//    ->hiddenInput()
    ->textInput(['placeholder' => $model->attributeLabels()['referral']])
    ->label(false);

echo Html::activeHiddenInput($model, 'http_referrer');

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Register'),
        'options' => [
            'class' => 'btn btn-primary btn-block'
        ],
    ]
]);

ActiveForm::end();
//echo Html::tag('p', Html::tag('small', Yii::t('app', 'Already have an account?')), ['class' => 'text-muted text-center']);
//echo Html::a(Yii::t('app', 'Login'), Url::toRoute("user/security/login", true), ['class' => 'btn btn-sm btn-white btn-block']);
