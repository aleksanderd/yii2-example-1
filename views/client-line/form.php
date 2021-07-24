<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ClientLine */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-line') . Yii::t('app', 'Phone lines'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add phone line');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create client-line-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update phone line') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update client-line-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
