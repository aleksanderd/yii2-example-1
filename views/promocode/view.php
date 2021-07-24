<?php

use app\widgets\hint\HintWidget;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Promocode */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->code;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Promocodes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);
}

echo HintWidget::widget(['message' => '#PromocodeView.hint']);
echo '<div class="promocode-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
//        'id',
        'code',
        'user.username',
        'newOnlyText',
        'created_at:datetime',
        'expiresText',
        'amount:currency',
        'countText',
        'description:ntext',
    ],
]);

echo GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $model->getActivations()->orderBy(['at' => SORT_DESC]),
        'pagination' => [
            'pageSize' => 20,
        ],
    ]),
    'columns' => [
        'at:datetime',
        'user.username',
        [
            'attribute' => 'userTransaction.amount',
            'format' => 'currency',
            'hAlign' => 'right',
        ],
    ],
]);

echo '</div>' . PHP_EOL; // class="promocode-view"
