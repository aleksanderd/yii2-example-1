<?php

use flyiing\helpers\Html;
use app\models\Tariff;

/* @var $this yii\web\View */
/* @var $tariff app\models\Tariff */

echo Html::tag('h2', $tariff->title);
echo Html::tag('i', $tariff->desc);
echo '<br><br>';

$limitCallback = function ($value, $postfix = '') {
    if ($value == 0) {
        $value = Yii::t('app', 'Unlimited');
    } else {
        $value .= $postfix;
    }
    return $value;
};
echo \yii\widgets\DetailView::widget([
    'model' => $tariff,
    'attributes' => [
        [
            'attribute' => 'price',
            'format' => 'currency'
        ],
        [
            'attribute' => 'lifetime',
            'value' => Tariff::getLifetimeReadable($tariff),
        ],
        [
            'attribute' => 'renewable',
            'value' => Tariff::getRenewableReadable($tariff),
        ],
        [
            'attribute' => 'queries',
            'value' => $limitCallback($tariff->queries),
        ],
        [
            'attribute' => 'minutes',
            'value' => $limitCallback($tariff->minutes),
        ],
        [
            'attribute' => 'messages',
            'value' => $limitCallback($tariff->messages),
        ],
        [
            'attribute' => 'space',
            'value' => $limitCallback($tariff->space, ' ' . Yii::t('app', 'Mb.')),
        ],
    ],
]);

echo '<br>';
echo Html::tag('i', $tariff->desc_details);
