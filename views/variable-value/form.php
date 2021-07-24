<?php

use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\VariableValue */

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Variable values'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add variable value');
    $this->params['breadcrumbs'][] = Yii::t('app', 'Add');
    echo '<div class="crud-create variable-value-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update variable value') . ': ' . $model->variable->name;
    $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
    echo '<div class="crud-update variable-value-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
