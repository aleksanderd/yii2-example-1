<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $tariffs app\models\Tariff[] */
/* @var $user app\models\User */

$this->title = Yii::t('app', 'Select tariff');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User tariffs'),
    'url' => ['index', 'user_id' => $user->id],
];
$this->params['breadcrumbs'][] = $this->title;

echo HintWidget::widget(['message' => '#UserTariffSelect.hint']);
echo '<div class="user-tariff-select row">' . PHP_EOL;
echo AlertFlash::widget();

foreach ($tariffs as $tariff) {
    $item = $this->render('_select_item', compact('tariff'));

    $addButton = [
        'tagName' => 'a',
        'encodeLabel' => false,
        'label' => Html::icon('cart-plus') . Yii::t('app', 'Add tariff'),
        'options' => [
            'class' => 'btn btn-success',
            'href' => Url::to(['add', 'tariff_id' => $tariff->id, 'user_id' => $user->id]),
        ],
    ];
    $buyButton = [
        'tagName' => 'a',
        'encodeLabel' => false,
        'label' => Html::icon('ok') . Yii::t('app', 'Buy tariff'),
        'options' => [
            'class' => 'btn btn-primary',
            'href' => Url::to(['add', 'tariff_id' => $tariff->id, 'user_id' => $user->id, 'pay' => true]),
        ],
    ];
    $item .= \yii\bootstrap\ButtonGroup::widget([
        'buttons' => [$addButton, $buyButton],
        'options' => [
            'class' => 'btn-group btn-group-justified'
        ],
    ]);

    echo Html::tag('span', $item, [
        'class' => 'col-lg-4 col-md-6',
    ]);
}

echo '</div>';
