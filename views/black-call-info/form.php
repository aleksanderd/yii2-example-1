<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\BlackCallInfo */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('black-call-info') . Yii::t('app', 'Call info blacklist'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add black call info');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Create');
    echo '<div class="crud-create black-call-info-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update black call info') . ': ' . $model->id . ': ' . $model->call_info;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update black-call-info-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
