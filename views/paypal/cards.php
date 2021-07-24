<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Credit card');
$this->params['breadcrumbs'][] = Html::icon('credit-card') . Yii::t('app', 'Credit cards');
$this->params['actions'] = \flyiing\helpers\UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add credit card'),
        'url' => ['add-card'],
    ],
]);

echo AlertFlash::widget();

echo GridView::widget([
    'dataProvider' => $cards,
    'columns' => [
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'attributes' => ['number', 'type'],
        ],
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'attributes' => ['first_name', 'last_name'],
        ],
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'attributes' => ['create_time', 'update_time'],
        ],
        [
            'label' => Yii::t('app', 'Actions'),
            'content' => function (\PayPal\Api\CreditCard $model) {
                $content = Html::a(Yii::t('app', 'Delete'), Url::to(['delete-card', 'id' => $model->id]), [
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('app', 'Are you sure to delete this credit card?'),
                ]);
                return $content;
            }
        ],
    ],
]);

//echo Html::tag('pre', print_r($cards, true));