<?php

use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ModalText */

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('modal-text') . Yii::t('app', 'Modal Texts'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="modal-text-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user_id',
        'language',
        'title',
        'm_title',
        'm_submit',
        'm_description:ntext',
    ],
]);

echo '</div>' . PHP_EOL; // class="modal-text-view"
