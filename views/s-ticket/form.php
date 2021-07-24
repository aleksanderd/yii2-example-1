<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\STicket */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('sticket') . Yii::t('app', 'Support tickets'),
    'url' => ['index'],
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add support ticket');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create sticket-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update support ticket') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update sticket-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
