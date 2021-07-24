<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VariableValueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Variable values');
$this->params['breadcrumbs'][] = Html::icon('variable-value') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add variable value'),
    ]
]);

echo HintWidget::widget(['message' => '#VariableValueIndex.hint']);
echo '<div class="variable-value-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    'variable.name',
];
if (Yii::$app->user->identity->isAdmin) {
    $columns[] = 'user.username';
}
$columns = array_merge($columns, [
    'site.title',
    'page.title',
    'value',
    ['class' => 'flyiing\grid\ActionColumn'],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="variable-value-index"
