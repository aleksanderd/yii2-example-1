<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;
use app\models\Payment;
use flyiing\widgets\AlertFlash;
use kartik\money\MaskMoney;
use yii\helpers\ArrayHelper;
use app\modules\payments\LogosAsset;

/* @var $this yii\web\View */
/* @var $model app\models\Payment */

$logosPrefix = LogosAsset::register($this)->baseUrl;

$this->title = Yii::t('app', 'Add funds');
$this->params['breadcrumbs'][] = Html::icon(Yii::$app->currencyCode) . $this->title;

echo AlertFlash::widget();

echo '<div class="payment-add">' . PHP_EOL;

$form = ActiveForm::begin();

echo $form->field($model, 'amount')->widget(MaskMoney::className(), [
    'pluginOptions' => [
        'affixesStay' => false,
        'allowNegative' => false,
    ],
])->label(Yii::t('app', 'Total amount to add') . ', ' . Html::icon(Yii::$app->currencyCode));

/** @var \app\modules\payments\Module $pModule */
$pModule = Yii::$app->getModule('payments');
$methods = $pModule->getMethods();

if (!isset($model->method)) {
    $items = [];
    foreach ($methods as $key => $value) {
        $items[$key] = $value['name'];
    }
    echo $form->field($model, 'method')->radioList($items);
} else {
    $method = $methods[$model->method];
    $name = $method['name'];
    $img = Html::tag('img', '', [
        'src' => $logosPrefix .'/'. strtolower($name) .'.png',
        'class' => 'img-thumbnail',
        'style' => 'max-height: 100px;',
    ]);
    echo $form->field($model, 'method', [
        //'enableLabel' => false,
        'labelOptions' => [
        ],
        'parts' => [
            '{input}' => $img
        ]
    ]);
}

echo $form->buttons();
ActiveForm::end();

echo '</div>' . PHP_EOL;
