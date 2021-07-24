<?php

use app\models\Payment;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Payments');
$this->params['breadcrumbs'][] = Html::icon('payment') . $this->title;

if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions([
        'create' => [
            'label' => Yii::t('app', 'Add payment'),
        ]
    ]);
} else {
    echo Html::tag('h3', Yii::t('app', 'Your current balance is: {0}', sprintf('%.02f', $user->balance)));
}

echo HintWidget::widget(['message' => '#PaymentIndex.hint']);
echo '<div class="payment-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'filterOptions' => ['style' => 'max-width: 40px;'],
        'content' => function (Payment $m) {
            return Html::a($m->id, ['view', 'id' => $m->id], ['data-pjax' => 0]);
        },
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{method}</strong><br>{at}',
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'method',
                'value' => function (Payment $m) {
                    return ArrayHelper::getValue(Payment::methodLabels(), $m->method, '-');
                },
            ],
            [
                'attribute' => 'at',
                'format' => 'datetime',
            ],
        ],
        'hAlign' => 'center',
    ],
    'description',
//    [
//        'attribute' => 'status',
//        'format' => 'raw',
//        'content' => function (Payment $m) {
//            return \app\helpers\ViewHelper::paymentStatusSpan($m);
//        },
//        'hAlign' => 'center',
//        'vAlign' => 'middle',
//    ],
    [
        'attribute' => 'amount',
        'filter' => false,
        'format' => 'currency',
        'content' => function (Payment $m) use ($user) {
            $value = Yii::$app->formatter->asCurrency($m->amount);
            if ($m->status < Payment::STATUS_COMPLETED) {
                if ($user->isAdmin) {
                    $url = Html::a(Yii::t('app', 'Complete'), ['force-complete', 'id' => $m->id], ['data-pjax' => 0]);
                    return $value .'<br>'. Html::tag('small', $url);
                } else {
                    return $value;
                }
            }
            $content = Html::tag('strong', Html::icon('payment-completed') . $value, ['class' => 'text-success']);
            $tid = ArrayHelper::getValue($m, 'transactions.0.id', 0);
            $tLink = Html::a(
                Yii::t('app', 'Transaction {id}', ['id' => $tid]),
                ['//transaction/view', 'id' => $tid],
                ['data-pjax' => 0, 'target' => '_blank']
            );
            $content .= '<br>' . Html::tag('small', $tLink);
            return $content;
        },
        'hAlign' => 'right',
    ],
];

if ($user->isAdmin) {
    $columns = array_merge([
        [
            'label' => Yii::t('app', 'Crucial user'),
            'attribute' => 'admin.username',
        ],
        [
            'class' => \app\widgets\grid\UserColumn::className(),
            'filterWidgetOptions' => [
                'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
            ],
            //'width' => '20%',
        ]
    ], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="transaction-index"
