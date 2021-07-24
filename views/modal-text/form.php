<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\ModalText */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('modal-text') . Yii::t('app', 'Modal Texts'),
    'url' => ['index'],
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add modal text');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create modal-text-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update modal text') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update modal-text-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
