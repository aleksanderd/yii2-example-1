<?php

use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $data array */

echo Html::beginTag('form', [
    'action' => 'https://paymaster.ru/direct/security/auth',
    'method' => 'post',
]);

$iat = time();
$key = 'gmcf11paymaster';
$data['type'] = 'rest';
$data['iat'] = $iat;
foreach ($data as $name => $value) {
    echo Html::hiddenInput($name, $value);
}

$signSrc = '';
foreach ($data as $pName => $pValue) {
    $signSrc .= $pValue . ';';
    // Или значение параметра кодировать?
    //$signSrc .= urlencode($pValue) . ';';
}
$signSrc .= $iat .';'. $key;
// Второй вариант, подписываем "тело поста", как в сказано доках:
//$signSrc = $post .';'. $iat .';'. $key;

$signHash = hash('sha256', $signSrc);
$sign = base64_encode($signHash);
// если закодировать подпись, то результат: Произошла ошибка при работе с сайтом, иногда Bad Gateway
//$sign = urlencode($sign);

echo Html::hiddenInput('sign', $sign);
echo Html::submitButton();
echo Html::endTag('form');
