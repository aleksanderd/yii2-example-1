<?php

use app\helpers\ViewHelper;
use app\models\Payout;
use app\models\Variable;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PayoutSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
$userMax = $user->partnerMaxPayout;
$payoutAllowed = $user->isPayoutAllowed;
$payoutMin = Variable::sGet('s.settings.payoutMin', $user->id);
$payoutMax = Variable::sGet('s.settings.payoutMax', $user->id);
$payoutInterval = Variable::sGet('s.settings.payoutInterval', $user->id);
$lastPayoutAt = $user->getPayouts()->where(['status' => Payout::STATUS_COMPLETE])->max('updated_at');
$fmt = Yii::$app->formatter;

$this->title = Yii::t('app', 'Payouts');
$this->params['breadcrumbs'][] = Html::icon('payout') . $this->title;

if ($payoutAllowed) {
    $this->params['actions'] = UniHelper::getModelActions([
        'create' => [
            'label' => Yii::t('app', 'Request payout'),
        ]
    ]);
}

echo HintWidget::widget(['message' => '#PayoutIndex.hint']);
echo '<div class="payout-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => ['created_at', 'updated_at'],
        'format' => 'datetime',
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'status',
        'content' => function (Payout $m) {
            return ViewHelper::payoutStatusSpan($m);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '{amount}<br>{transaction_id}',
        'attributes' => [
            [
                'attribute' => 'amount',
                'format' => 'raw',
                'content' => function (Payout $m) {
                    $result = Yii::$app->formatter->asCurrency($m->amount);
                    if ($m->status == Payout::STATUS_REJECTED) {
                        $result = Html::tag('s', $result);
                    } else if ($m->status >= Payout::STATUS_COMPLETE) {
                        $result = Html::tag('strong', $result);
                    }
                    return $result;
                },
            ],
            [
                'attribute' => 'transaction_id',
                'format' => 'raw',
                'value' => function (Payout $m) {
                    if (isset($m->transaction_id)) {
                        return Html::a(
                            '#' . $m->transaction_id,
                            Url::to(['/transaction/view', 'id' => $m->transaction_id]),
                            ['target' => '_blank']
                        );
                    } else {
                        return null;
                    }
                }
            ],
        ],
        'hAlign' => 'right',
        'vAlign' => 'middle',
    ],
    [
        'label' => Yii::t('app', 'Actions'),
        'content' => function (Payout $m) {
            return ButtonGroup::widget([
                'buttons' => UniHelper::actions2buttons(UniHelper::getPayoutActions($m)),
                'options' => [
                    'class' => 'btn-group btn-group-xs btn-group-vertical'
                ],
            ]);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
];

if ($user->isAdmin) {
    $columns = array_merge([
        'user.username',
    ], $columns);
    $columns[] = [
        'label' => Yii::t('app', 'Admin actions'),
        'content' => function (Payout $m) {
            return ButtonGroup::widget([
                'buttons' => UniHelper::actions2buttons(UniHelper::getPayoutAdminActions($m)),
                'options' => [
                    'class' => 'btn-group btn-group-xs btn-group-vertical'
                ],
            ]);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ];

    $totalPaid = Payout::find()->where(['status' => Payout::STATUS_COMPLETE])->sum('amount');
    $totalRequested = Payout::find()->where(['status' => Payout::STATUS_REQUEST])->sum('amount');
    $totalInProcess = Payout::find()->where(['status' => Payout::STATUS_IN_PROCESS])->sum('amount');
    $infoAdmin = '';
    $infoAdmin .= Html::tag('h5', Yii::t('app', 'Total in process: {sum}', ['sum' => $fmt->asCurrency($totalInProcess)]));
    $infoAdmin .= Html::tag('h5', Yii::t('app', 'Total requested: {sum}', ['sum' => $fmt->asCurrency($totalRequested)]));
    $infoAdmin .= Html::tag('h5', Yii::t('app', 'Total paid: {sum}', ['sum' => $fmt->asCurrency($totalPaid)]));
    echo Html::gsCols([
        Html::tag('h5', Yii::t('app', 'Total in process: {sum}', ['sum' => $fmt->asCurrency($totalInProcess)])),
        Html::tag('h5', Yii::t('app', 'Total in request: {sum}', ['sum' => $fmt->asCurrency($totalRequested)])),
        Html::tag('h5', Yii::t('app', 'Total paid: {sum}', ['sum' => $fmt->asCurrency($totalPaid)])),
    ]);
    echo '<div class="clearfix"></div><hr>';
}

$earned = $user->getReferrals()->sum('paid');
$paid = $user->getPayouts()->andWhere(['status' => Payout::STATUS_COMPLETE])->sum('amount');
$unpaid = ($t = $earned - $paid) > 0 ? $t : 0;

$infoUser = '';
$infoUser .= Html::tag('h5', Yii::t('app', 'Unpaid earns: {sum}', ['sum' => $fmt->asCurrency($unpaid)]));
$infoUser .= Html::tag('h5', Yii::t('app', 'Earned for all time: {sum}', ['sum' => $fmt->asCurrency($earned)]));
$infoUser .= Html::tag('h5', Yii::t('app', 'Paid for all time: {sum}', ['sum' => $fmt->asCurrency($paid)]));

$infoLimits = '';
$infoLimits .= Html::tag('h5', Yii::t('app', 'Minimal payout amount') .': '. $fmt->asCurrency($payoutMin));
$infoLimits .= Html::tag('h5', Yii::t('app', 'Maximal payout amount') .': '. $fmt->asCurrency($payoutMax));
$infoLimits .= Html::tag('h5', Yii::t('app', 'Minimal payout interval') .': '. $payoutInterval);
$infoStatus = '';
if ($userMax < $payoutMin) {
    $infoStatus .= Html::tag('h5', Yii::t('app', 'Payout minimum not reached yet.'), ['class' => 'text-danger']);
}
if (!$user->checkPayoutInterval()) {
    $allowTime = $fmt->asDatetime($lastPayoutAt + $payoutInterval * 86400);
    $infoStatus .= Html::tag('h5', Yii::t('app', 'Payout will be allowed at {dt}.', ['dt' => $allowTime] ), ['class' => 'text-danger']);
}
if (!$user->checkPayoutCount()) {
    $infoStatus .= Html::tag('h5', Yii::t('app', 'Maximum number of open payout requests reached.'), ['class' => 'text-danger']);
}
if ($payoutAllowed) {
    $infoStatus .= Html::tag('h5', Yii::t('app', 'Available for payout: {sum}', ['sum' => $fmt->asCurrency($userMax)]), ['class' => 'text-success']);
}

echo Html::tag('span', $infoStatus, ['class' => 'col-md-12']);
echo '<div class="clearfix"></div>';

echo Html::gsCols([$infoUser, $infoLimits]);
echo '<div class="clearfix"></div>';

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="payout-index"
