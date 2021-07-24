<?php

use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\Transaction */

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add transaction');
    $this->params['breadcrumbs'][] = Yii::t('app', 'Add');
    echo '<div class="crud-create transaction-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update transaction') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
    echo '<div class="crud-update transaction-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
