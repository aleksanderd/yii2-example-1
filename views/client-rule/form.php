<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-rule') . Yii::t('app', 'Rules'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add rule');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create client-rule-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update rule') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update client-rule-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
