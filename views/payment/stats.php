<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Payments statistics');
$this->params['breadcrumbs'][] = Html::icon('payment') . $this->title;

echo HintWidget::widget(['message' => '#PaymentStats.hint']);
echo '<div class="payment-stats">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_stats_search', ['model' => $searchModel]);

$columns = [

    'at' => [
        'class' => \app\widgets\grid\DateTimeColumn::className(),
        'period' => $searchModel->groupBy,
    ],
    'amount:currency',

];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="payments-stats"
