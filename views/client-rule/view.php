<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;
use app\models\ClientRule;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-rule') . Yii::t('app', 'Rules'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['lines', 'sites'],
]);
$this->params['actions'] = UniHelper::getModelActions($model, [
    'delete',
    'update',
]);

echo HintWidget::widget(['message' => '#ClientRuleView.hint']);
echo '<div class="client-rule-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    'id',
    [
        'attribute' => 'active',
        'value' => ArrayHelper::getValue(ClientRule::activeLabels(), $model->active),
    ],
    'priority',
    [
        'attribute' => 'site.title',
        'label' => Yii::t('app', 'Website'),
    ],
    'title',
    'description',
    [
        'attribute' => 'timezone',
        'value' => $model->timezoneLabel,
    ],
    [
        'label' => Yii::t('app', 'Phone lines'),
        'format' => 'raw',
        'value' => $this->render('_lines_list', ['lines' => $model->getLines()]),
    ],
];
if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo '</div>' . PHP_EOL; // class="client-rule-view"
