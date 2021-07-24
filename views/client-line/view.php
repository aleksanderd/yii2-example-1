<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ClientLine */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Html::icon('client-line') . Yii::t('app', 'Phone lines'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;

$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['rules', 'sites'],
]);
$this->params['actions'] = UniHelper::getModelActions($model, [
    'delete',
    'update',
]);

echo HintWidget::widget(['message' => '#ClientLineView.hint']);
echo '<div class="client-line-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    'id',
    //'type_id',
    'title',
    'info',
    'description',
];
if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo '</div>' . PHP_EOL; // class="client-line-view"
