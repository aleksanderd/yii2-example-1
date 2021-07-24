<?php

use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */

$this->title = Yii::t('app', 'Pay tariff') .' "'. $model->title .'"';
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User tariffs'),
    'url' => ['index', 'user_id' => $model->user_id],
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Pay');

echo '<div class="crud-create user-tariff-pay">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_form', compact('model', 'form'));

echo '</div>' . PHP_EOL;
