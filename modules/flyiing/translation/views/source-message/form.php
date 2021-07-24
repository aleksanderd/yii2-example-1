<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TSourceMessage */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('t-source-message') . Yii::t('app', 'Source messages'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add source message');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create t-source-message-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update source message') . ': ' . $model->id;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update t-source-message-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;