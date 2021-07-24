<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\Transaction */

$this->title = Yii::t('app', 'Transaction') .': #'. $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;
//$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo HintWidget::widget(['message' => '#TransactionView.hint']);
echo '<div class="transaction-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'at:datetime',
        'amount:currency',
        'user_id',
        [
            'label' => Yii::t('app', 'Payment'),
            'format' => 'raw',
            'value' => isset($model->payment) ? Html::a('#' . $model->payment_id, ['/payment/view', 'id' => $model->payment_id]) : '-',
        ],
        [
            'label' => Yii::t('app', 'Query'),
            'format' => 'raw',
            'value' => isset($model->query) ? Html::a('#' . $model->query_id, ['/client-query/view', 'id' => $model->query_id]) : '-',
        ],
        [
            'label' => Yii::t('app', 'Notification'),
            'format' => 'raw',
            'value' => isset($model->notification) ? Html::a('#' . $model->notification_id, ['/notification/view', 'id' => $model->notification_id]) : '-',
        ],
        'description',
        [
            'label' => Yii::t('app', 'Details'),
            'format' => 'raw',
            'value' => is_array($model->details) ? Html::tag('pre', print_r($model->details, true)) : '-',
        ],
    ],
]);

echo '</div>' . PHP_EOL; // class="transaction-view"
