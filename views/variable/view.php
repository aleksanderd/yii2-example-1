<?php

use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Variable */

$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('variable') . Yii::t('app', 'Variables'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#VariableView.hint']);
echo '<div class="variable-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user_id',
        'type_id',
        'name',
    ],
]);

echo '</div>' . PHP_EOL; // class="variable-view"
