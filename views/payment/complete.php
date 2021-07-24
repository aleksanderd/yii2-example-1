<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Payment */
/* @var $transaction app\models\Transaction */

$this->title = ($transaction === false) ?
    Yii::t('app', 'Funds not added') : Yii::t('app', 'Funds added successfully');
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('payment') . Yii::t('app', 'Payments'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('add-funds') . $this->title;

echo AlertFlash::widget();

echo '<div class="payment-complete">' . PHP_EOL;

echo Html::tag('h2', Yii::t('app', 'Payment details'));
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user.username',
        'at:datetime',
        'amount',
        [
            'attribute' => 'method',
            'value' => ArrayHelper::getValue($model->methodLabels(), $model->method, '-'),
        ],
        'description',
    ],
]);

if ($transaction !== false) {
    echo Html::tag('h2', Yii::t('app', 'Transaction details'));
    echo DetailView::widget([
        'model' => $transaction,
        'attributes' => [
            'id',
            'user.username',
            'at:datetime',
            'amount',
            'description',
        ],
    ]);
}

echo '</div>' . PHP_EOL;