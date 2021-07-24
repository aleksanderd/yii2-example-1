<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\Payout */

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Payouts'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add payout');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create payout-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update payout') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update payout-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
