<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use flyiing\widgets\ActiveForm;
use yii\helpers\Url;

/**
 * @var yii\web\View              $this
 * @var dektrium\user\models\User $user
 * @var dektrium\user\Module      $module
 */

$this->title = Yii::t('user', 'Register to GMCF');
$this->params['description'] = Yii::t('app', 'Create account to see it in action.');

$form = ActiveForm::begin([
    'id'                     => 'registration-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
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
    ->textInput(['placeholder' => Yii::t('app', 'e-mail')])
    ->label(false);

echo $form->field($model, 'username')
    ->textInput(['placeholder' => Yii::t('app', 'Username')])
    ->label(false);

echo $form->field($model, 'password')
    ->passwordInput(['placeholder' => Yii::t('app', 'Password')])
    ->label(false);

echo $form->field($model, 'referral');

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

echo Html::tag('p', Html::tag('small', Yii::t('app', 'Already have an account?')), ['class' => 'text-muted text-center']);
echo Html::a(Yii::t('app', 'Login'), Url::to('/user/security/login'), ['class' => 'btn btn-sm btn-white btn-block']);
