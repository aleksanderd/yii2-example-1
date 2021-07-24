<?php

use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TSourceMessage */

$this->title = $model->category .': '. $model->message;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('t-source-message') . Yii::t('app', 'Source messages'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="t-source-message-view">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'category',
        'message',
    ],
]);

echo '</div>' . PHP_EOL; // class="client-line-view"