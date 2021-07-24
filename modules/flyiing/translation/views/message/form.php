<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TMessage */

$this->params['breadcrumbs'][] = [
    'label' => Html::icon('t-message') . Yii::t('app', 'Translations'),
    'url' => ['index']
];

if($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add translation');
    $this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');
    echo '<div class="crud-create t-message-create">' . PHP_EOL;
} else {
    echo DetailView::widget([
        'model' => $model->source,
        'attributes' => ['id', 'category', 'message'],
    ]);
    $this->title = Yii::t('app', 'Update translation') . ': ' . $model->id;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('app', 'Update');
    echo '<div class="crud-update t-message-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;