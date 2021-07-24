<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\ClientPage */
/* @var $notifyModel app\models\variable\UNotify */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-page') . Yii::t('app', 'Pages'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add page');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create client-page-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update page') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update client-page-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', compact('model', 'notifyModel'));

echo '</div>' . PHP_EOL;
