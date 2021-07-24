<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ClientPage */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-page') . Yii::t('app', 'Pages'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['lines', 'rules', 'sites'],
]);
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#ClientPageView.hint']);
echo '<div class="client-page-view">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        [
            'attribute' => 'user.username',
            'label' => Yii::t('app', 'User'),
        ],
        [
            'attribute' => 'site.title',
            'label' => Yii::t('app', 'Website'),
        ],
        'typeLabel',
        'title',
        'pattern',
    ],
]);

echo '</div>' . PHP_EOL; // class="client-page-view"
