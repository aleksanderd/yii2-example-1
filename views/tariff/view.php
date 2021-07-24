<?php

use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;
use app\models\Tariff;

/* @var $this yii\web\View */
/* @var $model app\models\Tariff */

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('tariff') . Yii::t('app', 'Tariffs'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#TariffView.hint']);
echo '<div class="tariff-view">' . PHP_EOL;

echo AlertFlash::widget();

$limitCallback = function ($value) {
    if ($value == 0) {
        $value = Yii::t('app', 'Unlimited');
    }
    return $value;
};

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'title',
        'desc',
        [
            'attribute' => 'renewable',
            'value' => Tariff::getRenewableReadable($model),
        ],
        'price:currency',
        [
            'attribute' => 'lifetime',
            'value' => Tariff::getLifetimeReadable($model),
        ],
        [
            'attribute' => 'queries',
            'value' => $limitCallback($model->queries),
        ],
        [
            'attribute' => 'minutes',
            'value' => $limitCallback($model->minutes),
        ],
        [
            'attribute' => 'messages',
            'value' => $limitCallback($model->messages),
        ],
        [
            'attribute' => 'space',
            'value' => $limitCallback($model->space),
        ],
        'desc_details:ntext',
        'desc_internal:ntext',
        'created_at:datetime',
        'updated_at:datetime',
    ],
]);

echo '</div>' . PHP_EOL; // class="tariff-view"
