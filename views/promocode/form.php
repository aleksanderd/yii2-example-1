<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\Promocode */

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Promocodes'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add promocode');
    $this->params['breadcrumbs'][] = Yii::t('app', 'Add');
    echo '<div class="crud-create promocode-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update promocode') . ': ' . $model->code;
    $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
    echo '<div class="crud-update promocode-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
