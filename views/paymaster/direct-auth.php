<?php

use app\widgets\ActiveForm;
use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$form = ActiveForm::begin([
    'action' => 'https://paymaster.ru/direct/security/auth',
    'method' => 'get',
]);

$date = new \DateTime('now', new \DateTimeZone('UTC'));
$iat = $date->format(DATE_ISO8601);
echo Html::hiddenInput('type', 'rest');
//echo Html::hiddenInput('iat', $iat);
echo Html::hiddenInput('response_type', 'code');
echo Html::hiddenInput('client_id', ArrayHelper::getValue(Yii::$app->params, 'paymaster.LMI_MERCHANT_ID', ''));
echo Html::hiddenInput('redirect_uri', Url::to(['paymaster-direct-token'], true));
echo Html::hiddenInput('scope', '3');
//echo Html::hiddenInput('sign', $sign);

echo $form->buttons();

ActiveForm::end();
