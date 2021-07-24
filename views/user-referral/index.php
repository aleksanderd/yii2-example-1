<?php

use app\helpers\ViewHelper;
use app\models\ReferralUrl;
use app\models\UserReferral;
use app\models\Variable;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserReferralSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'User referrals');
$this->params['breadcrumbs'][] = Html::icon('user-referral') . $this->title;

$partnerId = $user->id;

if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions([
        'create' => [
            'label' => Yii::t('app', 'Add user referral'),
        ]
    ]);
}

echo HintWidget::widget(['message' => '#UserReferralIndex.hint']);
echo '<div class="user-referral-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$referralScheme = Variable::sGet('u.settings.referralScheme', $partnerId);
$referralPercentFirst = Variable::sGet('s.settings.referralPercentFirst', $partnerId);
$referralPercent = Variable::sGet('s.settings.referralPercent', $partnerId);

$infoBase = Html::tag('h5', Yii::t('app', 'Your partner ID: {id}', ['id' => $partnerId]));
$defaultUrl = ReferralUrl::defaultReferralUrl($partnerId);
$referralUrl = $defaultUrl->url;
$infoBase .= Html::tag('h5', Yii::t('app', 'Your default referral url: {url}', [
    'url' => Html::a($referralUrl, $referralUrl, ['target' => '_blank']),
]));
$infoPct = Html::tag('h5', Yii::t('app', 'Your first payment percent is: {pct}%', ['pct' => $referralPercentFirst]));
$infoPct .= Html::tag('h5', Yii::t('app', 'Your lifetime percent is: {pct}%', ['pct' => $referralPercent]));

$cols = Html::tag('div', $infoBase, ['class' => 'col-md-6']);
$cols .= Html::tag('div', $infoPct, ['class' => 'col-md-6']);
echo $cols;
echo '<div class="clearfix"></div>';

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{user_id}</strong><br><small>{user.created_at}</small>',
        'attributes' => [
            [
                'attribute' => 'user_id',
                'content' => function (UserReferral $m) {
                    $label = $m->user->username;
                    return Html::a($label, ['view', 'partner_id' => $m->partner_id, 'user_id' => $m->user_id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'user.created_at',
                'format' => 'datetime',
            ],
        ],
        'hAlign' => 'left',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'status',
                'content' => [ViewHelper::className(), 'UserReferralStatusSpan'],
            ],
        ],
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'scheme',
        'content' => [ViewHelper::className(), 'UserReferralScheme'],
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \app\widgets\grid\UserManageColumn::className(),
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '{referralPaid}<br><strong>{paid}</strong>',
        'attributes' => [
            [
                'attribute' => 'referralPaid',
                'format' => 'currency',
            ],
            [
                'attribute' => 'paid',
                'format' => 'currency',
            ],
        ],
        'hAlign' => 'right',
    ],
];
if ($user->isAdmin) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
            'attribute' => 'partner',
            'label' => Yii::t('app', 'Partner'),
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
    'responsive' => false,
]);

echo '</div>' . PHP_EOL; // class="user-referral-index"
