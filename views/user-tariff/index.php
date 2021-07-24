<?php

use yii\data\ActiveDataProvider;
use flyiing\helpers\Html;
use app\widgets\hint\HintWidget;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\grid\ActionColumn;
use kartik\grid\GridView;
use app\models\Tariff;
use app\models\UserTariff;
use app\helpers\DataHelper;
use yii\helpers\ArrayHelper;
use app\models\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserTariffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $active \yii\db\ActiveQuery */
/* @var $ready \yii\db\ActiveQuery */
/* @var $unpaid \yii\db\ActiveQuery */
/* @var $finished \yii\db\ActiveQuery */
/* @var $user \app\models\User */

$this->title = Yii::t('app', 'User tariffs');
$this->params['breadcrumbs'][] = $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add tariff'),
        'url' => ['select', 'user_id' => $user->id],
    ],
    'admin' => [
        'label' => Yii::t('app', 'Administration'),
        'url' => ['admin'],
        'options' => ['class' => 'btn-info']
    ],
]);

echo HintWidget::widget(['message' => '#UserTariffIndex.hint']);
echo '<div class="user-tariff-index">' . PHP_EOL;
echo AlertFlash::widget();

if (YII_ENV_DEV) {
    $link = Html::a('Active tariff dev form', ['dev-active', 'user_id' => $user->id], [
        'class' => 'pull-right',
    ]);
    echo Html::tag('div', $link, ['class' => 'row']);
}

/** @var \app\models\User $sysUser */
$sysUser = Yii::$app->user->identity;

if ($sysUser->isAdmin) {
    $users = User::find()->orderBy('username')->all();
    $select = \kartik\select2\Select2::widget([
        'name' => 'user_id',
        'value' => $user->id,
        'data' => ArrayHelper::map($users, 'id', 'username'),
//        'pluginOptions' => [
//            'width' => '300px',
//        ],
    ]);
    //echo Html::tag('span', $select, ['class' => 'col-md-3']);
    $button = \yii\bootstrap\Button::widget([
        'label' => Yii::t('app', 'Change user'),
        'options' => [
            'type' => 'submit',
            'class' => 'btn btn-primary',
        ],
    ]);
    echo Html::beginTag('form');
    echo Html::beginTag('div', ['class' => 'row']);
    echo Html::tag('span', $select, ['class' => 'col-lg-2 col-sm-5']);
    echo Html::tag('span', $button, ['class' => 'col-md-2']);
    echo Html::endTag('div');
    echo Html::endTag('form');
}

$baseColumns = [
    'id',
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'headerTemplate' => Yii::t('app', 'Base tariff'),
        'template' => '<strong>{tariff.title}</strong><br><small>{tariff.desc}<br>{renew}</small>',
        'attributes' => [
            'tariff.title',
            'tariff.desc',
            [
                'attribute' => 'renew',
                'format' => 'raw',
                'value' => function (UserTariff $m) {
                    if (!$m->renewable || $m->status < 0) {
                        return '';
                    }
                    $content = '';
                    if ($m->renew) {
                        $aLabel = Yii::t('app', 'disable');
                        $content .= Html::tag('span', Yii::t('app', 'Auto renew enabled'), [
                            'class' => 'label label-info'
                        ]);
                    } else {
                        $aLabel = Yii::t('app', 'enable');
                        $content .= Yii::t('app', 'Auto renew disabled');
                    }
                    if ($m->status >= UserTariff::STATUS_READY) {
                    }
                    $content .= ' (' . Html::a($aLabel, ['toggle-renew', 'id' => $m->id, 'value' => $m->renew ? 0 : 1]) .')';
                    return $content;
                },
            ]
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{price}</strong><br>{lifetime}<br>{renewable}',
        'attributes' => [
            [
                'attribute' => 'price',
                'format' => 'currency',
            ],
            [
                'attribute' => 'lifetime',
                'value' => function (UserTariff $m) {
                    return $m->getAttributeLabel('lifetime') .': '. Tariff::getLifetimeReadable($m);
                },

            ],
            [
                'attribute' => 'renewable',
                'value' => function (UserTariff $m) {
                    return $m->getAttributeLabel('renewable') .': '. Tariff::getRenewableReadable($m);
                },
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => Yii::t('app', 'Minutes') . ': {seconds_used} '. Yii::t('app', 'of') .' {seconds}<br>' .
            Yii::t('app', 'Messages') . ': {messages_used} '. Yii::t('app', 'of') .' {messages}',
        'headerTemplate' => 'Used',
        'attributes' => [
            [
                'label' => Yii::t('app', 'Minutes'),
                'attribute' => 'seconds',
                'value' => function (UserTariff $m) {
                    return intval($m->seconds) > 0 ?
                        DataHelper::durationToText($m->seconds) .' ' : Yii::t('app', 'unlimited');
                },
            ],
            [
                'label' => Yii::t('app', 'Used'),
                'attribute' => 'seconds_used',
                'value' => function (UserTariff $m) {
                    return DataHelper::durationToText($m->seconds_used);
                },
            ],
            [
                'attribute' => 'messages',
                'value' => function (UserTariff $m) {
                    return intval($m->messages) > 0 ? $m->messages : Yii::t('app', 'unlimited');
                },
            ],
            [
                'label' => Yii::t('app', 'Used'),
                'attribute' => 'messages_used',
            ],
        ],
    ],
];

if ($sysUser->isAdmin) {
    // TODO добавить админское меню: удаление, завершение и тд
    $adminColumns = [
        [
            'class' => ActionColumn::className(),
            'header' => Yii::t('app', 'Admin actions'),
            'template' => '<div class="btn-group btn-group-xs btn-group-vertical" role="group">{finish}</div>',
            'buttons' => [
                'finish' => function ($url, UserTariff $model, $key) {
                    $btnOpts = [
                        'class' => 'btn-danger',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('app', 'Are you sure you want to finish this tariff?'),
                    ];
                    return ActionColumn::renderDefaultButton(Html::icon('stop') . Yii::t('app', 'Finish'),
                        $url, $model, $key, $btnOpts);
                },
            ],
        ],
    ];
} else {
    $adminColumns = [];
}

// Активные тарифы
$activeCount = $active->count();
if ($activeCount > 0) {
    echo Html::tag('h3', Yii::t('app', 'Active tariffs'));
    echo GridView::widget([
        'dataProvider' => new ActiveDataProvider(['query' => $active]),
        'columns' => array_merge($baseColumns, [
            [
                'class' => \flyiing\grid\DataColumn::className(),
                'attributes' => [
                    [
                        'attribute' => 'started_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'lifetimeEnd',
                        'format' => 'datetime',
                    ],
                ],
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '<div class="btn-group btn-group-xs btn-group-vertical" role="group">{deactivate}</div>',
                'buttons' => [
                    'deactivate' => function ($url, UserTariff $model, $key) use ($activeCount) {
                        if ($model->lifetime > 0 && $model->started_at <= time()
                            || $activeCount == 1 && $model->renew) {
                            return '';
                        }
                        $btnOpts = [
                            'class' => 'btn-danger',
                            'data-confirm' => Yii::t('app', 'Are you sure you want to deactivate this tariff?'),
                        ];
                        return ActionColumn::renderDefaultButton(Html::icon('tariff-deactivate') . Yii::t('app', 'Deactivate'),
                            $url, $model, $key, $btnOpts);
                    },
                ],
            ],
        ], $adminColumns),
    ]);
} else {
    echo Html::tag('div', Yii::t('app', 'You have no any active tariff.'), [
        'class' => 'alert alert-warning',
    ]);
}

if ($ready->count() > 0) {
    $ltActive = $active->andWhere(['>', 'lifetime', 0])->all();
    echo Html::tag('h3', Yii::t('app', 'Ready tariffs'));
    echo GridView::widget([
        'dataProvider' => new ActiveDataProvider(['query' => $ready]),
        'columns' => array_merge($baseColumns, [
            [
                'class' => ActionColumn::className(),
                'template' => '<div class="btn-group btn-group-xs btn-group-vertical" role="group">{activate}{enable-renew}</div>',
                'buttons' => [
                    'activate' => function ($url, UserTariff $model, $key) use ($ltActive) {
                        $btnOpts = ['class' => 'btn-success'];
                        if ($model->lifetime > 0) {
                            if (count($ltActive) > 0) {
                                return '';
                            }
                            $btnOpts['data-confirm'] = Yii::t('app', 'This tariff is time limited, so can not be deactivated. Are you sure you want to activate it?');
                        }
                        return ActionColumn::renderDefaultButton(Html::icon('tariff-activate') . Yii::t('app', 'Activate'),
                            $url, $model, $key, $btnOpts);
                    },
                ],
            ],
        ], $adminColumns),
    ]);
} else {
    if ($active->count() < 1) {
        echo Html::tag('div', Yii::t('app', 'You need to buy some tariff to active it. Press the button above.'), ['class' => 'alert alert-warning']);
    }
}

if ($unpaid->count() > 0) {
    echo Html::tag('h3', Yii::t('app', 'Unpaid tariffs'));
    echo GridView::widget([
        'dataProvider' => new ActiveDataProvider(['query' => $unpaid]),
        'columns' => array_merge($baseColumns, [
            [
                'class' => ActionColumn::className(),
                'template' => '<div class="btn-group btn-group-xs btn-group-vertical" role="group">{pay}{delete}</div>',
                'buttons' => [
                    'pay' => function ($url, UserTariff $model, $key) use ($activeCount) {
                        if ($model->user->balance < $model->price) {
                            return '';
                        }
                        return ActionColumn::renderDefaultButton(Html::icon('plus') . Yii::t('app', 'Pay'),
                            $url, $model, $key, ['class' => 'btn btn-primary']);
                    },
                    'delete' => function ($url, UserTariff $model, $key) use ($activeCount) {
                        $btnOpts = [
                            'class' => 'btn btn-danger',
                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this tariff?'),
                            'data-method' => 'post',
                        ];
                        return ActionColumn::renderDefaultButton(Html::icon('model-delete') . Yii::t('app', 'Delete'),
                            $url, $model, $key, $btnOpts);
                    },
                ],
            ],
        ]),
    ]);
}

if ($finished->count() > 0) {
    echo Html::tag('h3', Yii::t('app', 'Finished tariffs'));
    echo GridView::widget([
        'dataProvider' => new ActiveDataProvider(['query' => $finished]),
        'columns' => array_merge($baseColumns, [
            [
                'class' => \flyiing\grid\DataColumn::className(),
                'attributes' => [
                    [
                        'attribute' => 'started_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'finished_at',
                        'format' => 'datetime',
                    ],
                ],
            ],

        ], $adminColumns),
    ]);
}

echo '</div>' . PHP_EOL; // class="user-tariff-index"
