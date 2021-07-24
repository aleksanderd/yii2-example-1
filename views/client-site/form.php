<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ClientSite */
/* @var $notifyModel app\models\variable\UNotify */

$this->params['breadcrumbs'][] = ['label' => Html::icon('client-site') . Yii::t('app', 'Websites'), 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add website');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create client-site-create">' . PHP_EOL;
} else {
    $this->title = Yii::t('app', 'Update website') . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update client-site-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', compact('model', 'notifyModel'));

echo '</div>' . PHP_EOL;
