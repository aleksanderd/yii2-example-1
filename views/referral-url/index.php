<?php

use app\models\ReferralUrl;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ReferralUrlSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $defaultUrl \app\models\ReferralUrl */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Referral urls');
$this->params['breadcrumbs'][] = Html::icon('referral-url') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add referral url'),
    ]
]);

echo HintWidget::widget(['message' => '#ReferralUrlIndex.hint']);
echo '<div class="referral-url-index">' . PHP_EOL;
echo AlertFlash::widget();

$infoBase = Html::tag('h5', Yii::t('app', 'Your partner ID: {id}', ['id' => $user->id]));
$cols = Html::tag('span', $infoBase, ['class' => 'col-md-12']);
$infoUrl = Html::tag('h5', Yii::t('app', 'Your default referral url: ') . Html::a($defaultUrl->url, $defaultUrl->url));
$cols .= Html::tag('span', $infoUrl, ['class' => 'col-md-12']);
// echo $this->render('_search', ['model' => $searchModel]);
echo $cols;
echo '<div class="clearfix"></div>';

$css =<<<CSS
td strong.code {
    font-size: 110%;
    font-weight: bold;
    color: black;
    opacity: 0.7;
}
td span.gift {
    font-weight: bold;
    color: darkcyan;
}
td span.paid {
    font-weight: bold;
    color: darkgreen;
}
CSS;
$this->registerCss($css);

$columns = [
    [
        'attribute' => 'id',
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{title}</strong><br><small>{created_at}</small>',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function (ReferralUrl $m) {
                    return Html::a($m->titleText, Url::to(['view', 'id' => $m->id]), ['data-pjax' => 0]);
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<span class="pull-right gift">{gift_amount}</span><strong class="code">{code}</strong><br><small>{url}</small>',
        'attributes' => [
            [
                'attribute' => 'code',
            ],
            [
                'attribute' => 'url',
                'format' => 'url',
            ],
            [
                'attribute' => 'gift_amount',
                'encodeLabel' => false,
                'label' => Html::icon('gift'),
                'content' => function (ReferralUrl $m) {
                    if ($m->gift_amount > 0) {
                        return Html::icon('gift') . Yii::$app->formatter->asCurrency($m->gift_amount);
                    } else {
                        return '-';
                    }
                }
            ],
        ],
        'hAlign' => 'left',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '{stats.visits}<br><strong>{stats.registered}</strong>',
        'attributes' => [
            'stats.visits',
            'stats.registered',
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{stats.gifts_activated}<br><span class="gift">{stats.gifts_paid}</span></strong>',
        'attributes' => [
            'stats.gifts_activated',
            [
                'attribute' => 'stats.gifts_paid',
                'format' => 'currency',
            ],
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{stats.active}<br><span class="paid">{stats.paid}</span></strong>',
        'attributes' => [
            'stats.active',
            [
                'attribute' => 'stats.paid',
                'format' => 'currency',
            ],
        ],
        'hAlign' => 'right',
    ],
];

if ($user->isAdmin) {
    array_unshift($columns, 'user.username');
    $columns[] = ['class' => 'flyiing\grid\ActionColumn'];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="referral-url-index"
