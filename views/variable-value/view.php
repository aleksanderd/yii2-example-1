<?php

use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\VariableValue */

$this->title = $model->variable->name;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('variable-value') . Yii::t('app', 'Variable values'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#VariableValueView.hint']);
echo '<div class="variable-value-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'variable.name',
        'user.username',
        'site.title',
        'page.title',
        'value',
    ],
]);

echo '</div>' . PHP_EOL; // class="variable-value-view"
