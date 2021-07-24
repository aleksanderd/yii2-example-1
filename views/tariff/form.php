<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\Tariff */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('tariff') . Yii::t('app', 'Tariffs'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add tariff');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create tariff-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update Tariff') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update tariff-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
