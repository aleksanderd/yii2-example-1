<?php

use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Tariffs'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add user tariff');
    $this->params['breadcrumbs'][] = Yii::t('app', 'Create');
    echo '<div class="crud-create user-tariff-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Edit user tariff') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
    echo '<div class="crud-update user-tariff-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
