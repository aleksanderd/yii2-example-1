<?php

use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model flyiing\translation\models\TMessage */

$this->title = $model->id .': '. $model->translation;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('t-message') . Yii::t('app', 'Translations'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="t-message-view">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'source.category',
        'source.message',
        'language',
        'translation',
    ],
]);

echo '</div>' . PHP_EOL; // class="client-line-view"