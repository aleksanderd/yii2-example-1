<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VariableSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Variables');
$this->params['breadcrumbs'][] = Html::icon('variable') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add variable'),
    ]
]);

echo HintWidget::widget(['message' => '#VariableIndex.hint']);
echo '<div class="variable-index">' . PHP_EOL;
echo AlertFlash::widget();
echo $this->render('_search', ['model' => $searchModel]);

$columns = ['id'];
if (Yii::$app->user->identity->isAdmin) {
    $columns[] = 'user.username';
}
$columns = array_merge($columns, [
    'type',
    'name',
    ['class' => 'flyiing\grid\ActionColumn'],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="variable-index"
