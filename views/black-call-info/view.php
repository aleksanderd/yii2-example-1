<?php

use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BlackCallInfo */

$modelTitle = $model->id .': '. $model->call_info;
$this->title = Yii::t('app', 'Black call info: ') .': '. $modelTitle;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('black-call-info') . Yii::t('app', 'Call info blacklist'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $modelTitle;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="black-call-info-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user.username',
        'call_info',
        'comment:ntext',
    ],
]);

echo '</div>' . PHP_EOL; // class="black-call-info-view"
