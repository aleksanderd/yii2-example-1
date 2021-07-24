<?php

use flyiing\helpers\Html;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQueryTest */

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-query-test') . Yii::t('app', 'Query tests'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, [
    'update',
    'run' => [
        'icon' => 'run',
        'label' => Yii::t('app', 'Run'),
        'url' => ['run', 'id' => $model->id],
    ],
    'delete',
]);

echo '<div class="client-query-test-view">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user_id',
        'site_id',
        'at:datetime',
        'call_info',
//        'data:ntext',
        'title',
        'description',
//        'options:ntext',
    ],
]);

echo '</div>' . PHP_EOL; // class="client-query-test-view"
