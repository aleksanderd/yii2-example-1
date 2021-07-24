<?php

/* @var $this yii\web\View */

use app\modules\payments\LogosAsset;
use flyiing\helpers\Html;

$logosBundle = LogosAsset::register($this);
$logosPrefix = $logosBundle->baseUrl;

$logos = [
    'webmoney.gif',
    'alfabank.gif',
    'brs.gif',
    '47_logo_contact.gif',
    'euroset.png',
    'psb-paymaster.png',
    'svyaznoi.png',
    '92_VisaMastercardLogo.gif',
    '121_Logo_QBank-by-SvzBank_w_CMYK-01_125x70.png',
    '126_sberbank_1.png',
    '132_129_russianpost.png',
    'qiwi_h.png',
    'yandex.gif',
];

foreach ($logos as $l) {
    $img = Html::img($logosPrefix .'/paymaster/'. $l, ['class' => 'img-thumbnail paymaster-logo']);
    echo $img;
}