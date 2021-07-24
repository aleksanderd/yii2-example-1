<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use app\models\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Transactions');
$this->params['breadcrumbs'][] = $this->title;

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions([
        'create' => [
            'label' => Yii::t('app', 'Add transaction'),
        ]
    ]);
} else {
    echo Html::tag('h3', Yii::t('app', 'Your current balance is: {0}', sprintf('%.02f', $user->balance)));
}

echo HintWidget::widget(['message' => '#TransactionIndex.hint']);
echo '<div class="transaction-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'content' => function (Transaction $m) {
            return Html::a('#' . $m->id, ['view', 'id' => $m->id], ['data-pjax' => 0]);
        },
        'hAlign' => 'right',
    ],
    [
        'attribute' => 'at',
        'filter' => false,
        'format' => 'datetime',
        'hAlign' => 'center',
    ],
    [
        'label' => Yii::t('app', 'Transaction links'),
        'content' => function (Transaction $m) {

            $btnConfig = [
                'tagName' => 'a',
                'encodeLabel' => false,
                'options' => [
                    'target' => '_blank',
                    'data-pjax' => 0,
//                    'class' => 'btn btn-default',
                ],
            ];
            $buttons = [];
            if ($p = $m->payment) {
                $buttons[] = ArrayHelper::merge($btnConfig, [
                    'label' => Yii::t('app', 'Payment') .' #'. $p->id,
                    'options' => [
                        'href' => Url::to(['/payment/view', 'id' => $p->id]),
                    ],
                ]);
            }
            if ($q = $m->query) {
                $buttons[] = ArrayHelper::merge($btnConfig, [
                    'label' => Yii::t('app', 'Query') .' #'. $q->id,
                    'options' => [
                        'href' => Url::to(['/client-query/view', 'id' => $q->id]),
                    ],
                ]);
            }
            if ($n = $m->notification) {
                $buttons[] = ArrayHelper::merge($btnConfig, [
                    'label' => Yii::t('app', 'Notification') .' #'. $n->id,
                    'options' => [
                        'href' => Url::to(['/notification/view', 'id' => $n->id]),
                    ],
                ]);
            }
            if ($t = $m->userTariff) {
                $buttons[] = ArrayHelper::merge($btnConfig, [
                    'label' => /* Yii::t('app', 'Tariff') .' #'. $t->id .' '. */ $t->title,
                    'options' => [
                        'href' => Url::to(['/user-tariff/view', 'id' => $t->id]),
                    ],
                ]);
            }
            return \yii\bootstrap\ButtonGroup::widget([
                'buttons' => $buttons,
                'options' => [
                    'class' => 'btn-group btn-group-xs',
                ],
            ]);
        },
    ],
    'description',
    [
        'attribute' => 'amount',
        'filter' => false,
        'format' => 'currency',
        'contentOptions' => function (Transaction $m) {
            return [
                'class' => $m->amount < 0 ? 'minus' : 'plus',
            ];
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
//    'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="transaction-index"
