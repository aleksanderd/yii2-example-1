<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BlackCallInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Call info blacklist');
$this->params['breadcrumbs'][] = Html::icon('black-call-info') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add black call info'),
    ]
]);

echo HintWidget::widget(['message' => '#BlackCallInfoIndex.hint']);
echo '<div class="black-call-info-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    'call_info',
    'comment',
    ['class' => \yii\grid\ActionColumn::className()],
];
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
if ($user->isAdmin) {
    $columns = array_merge(['user.username'], $columns);
}
$columns = array_merge(['id'], $columns);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="black-call-info-index"
