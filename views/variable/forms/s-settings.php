<?php

use flyiing\helpers\Html;
use app\models\Tariff;
use kartik\select2\Select2;
use kartik\touchspin\TouchSpin;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form flyiing\widgets\ActiveForm */

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'System settings'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';
echo $form->field($model, 'title')->input('text');
echo $form->field($model, 'url')->input('text');
echo $form->field($model, 'baseUrl')->input('text');

echo $form->field($model, 'supportEmail')->input('text');
echo $form->field($model, 'salesEmail')->input('text');

$tariffs = Tariff::find()
    //->where(['price' => 0])
    ->orderBy('title')
    ->all();

echo $form->field($model, 'trialTariff')->widget(Select2::className(), [
    'data' => ArrayHelper::map($tariffs, 'id', 'title'),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'No trial'),
    ],
]);
echo $form->field($model, 'trialActivation')->widget(Select2::className(), [
    'data' => [
        0 => Yii::t('app', 'Allow the user to activate the tariff manually'),
        90 => Yii::t('app', 'Auto activate on first call query'),
        100 => Yii::t('app', 'Auto activate the tariff immedaily'),
    ],
    'hideSearch' => true,
]);

$timeoutOpts = [
    'pluginOptions' => [
        'min' => 1,
        'max' => 9999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 'days'),
    ],
];
echo $form->field($model, 'timeoutNewUser')->widget(TouchSpin::className(), $timeoutOpts);
echo $form->field($model, 'timeoutActiveUser')->widget(TouchSpin::className(), $timeoutOpts);
echo $form->field($model, 'timeoutInactiveUser')->widget(TouchSpin::className(), $timeoutOpts);
echo '</div>'; // panel-body
echo '</div>'; // panel

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Referral system settings'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';

$moneyOpts = [
    'pluginOptions' => [
        'min' => 0,
        'max' => 100000,
        'decimals' => 0,
        'step' => 1,
        'postfix' => Yii::t('app', 'RUR'),
    ],
];
echo $form->field($model, 'referralFixedFirst')->widget(TouchSpin::className(), $moneyOpts);
$pctOpts = [
    'pluginOptions' => [
        'min' => 1,
        'max' => 100,
        'boostat' => 5,
        'postfix' => '%',
    ],
];
echo $form->field($model, 'referralPercentFirst')->widget(TouchSpin::className(), $pctOpts);
echo $form->field($model, 'referralPercent')->widget(TouchSpin::className(), $pctOpts);
echo $form->field($model, 'referralTimeLimit')->widget(TouchSpin::className(), $timeoutOpts);
echo $form->field($model, 'referralGiftMax')->widget(TouchSpin::className(), $moneyOpts);
echo $form->field($model, 'referralGiftPaymentsRequired')->widget(TouchSpin::className(), $moneyOpts);

echo $form->field($model, 'payoutMin')->widget(TouchSpin::className(), $moneyOpts);
echo $form->field($model, 'payoutMax')->widget(TouchSpin::className(), $moneyOpts);
echo $form->field($model, 'payoutInterval')->widget(TouchSpin::className(), $timeoutOpts);

echo $form->field($model, 'referralAgreement')->textarea([
    'rows' => 15,
]);
echo $form->field($model, 'referralAgreementVersion')->textInput();

echo '</div>'; // panel-body
echo '</div>'; // panel
