<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use app\helpers\UniHelper;
use app\models\User;
use dektrium\user\models\UserSearch;
use flyiing\widgets\AlertFlash;
use yii\data\ActiveDataProvider;
use app\widgets\grid\GridView;
use yii\jui\DatePicker;
use yii\web\View;
use flyiing\helpers\Html;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Users');
$this->params['breadcrumbs'][] = Html::icon('user') . $this->title;

$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('user', 'Create a user account'),
    ]
]);

echo AlertFlash::widget();

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            'id',
            [
                'attribute' => 'blocked_at',
                'label' => Yii::t('app', 'Blk'),
                'content' => function (User $m) {
                    $options = [
                        //'data-pjax' => 'w0-pjax',
                        'data-method' => 'post',
                    ];
                    if ($m->isBlocked) {
                        $icon = 'toggle-off';
                        $options['data-confirm'] = Yii::t('user', 'Are you sure you want to unblock this user?');
                        Html::addCssClass($options, 'text-danger');
                    } else {
                        $icon = 'toggle-on';
                        $options['data-confirm'] = Yii::t('user', 'Are you sure you want to block this user?');
                        Html::addCssClass($options, 'text-success');
                    }
                    return Html::a(Html::icon($icon, '{i}'), ['block', 'id' => $m->id], $options);
                },
            ],
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{username}</strong><br><small>{email}</small>',
        'filter' => Html::activeTextInput($searchModel, 'ine', [
            'class' => 'form-control compact',
            'placeholder' => $searchModel->attributeLabels()['ine'],
        ]),
        'attributes' => [
            [
                'attribute' => 'username',
                'content' => function (User $m) {
                    return Html::a($m->username, ['update', 'id' => $m->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'email',
                'format' => 'email',
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '{created_at}<br><small>{registration_ip}</small>',
        'filter' => false,
        //'filterTemplate' => '{created_at}{registration_ip}',
        'attributes' => [
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
                    'model'      => $searchModel,
                    'attribute'  => 'created_at',
                    'dateFormat' => 'php:Y-m-d',
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
            ],
            [
                'attribute' => 'registration_ip',
            ],
        ],
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'header' => Yii::t('user', 'Confirmation'),
        'value' => function ($model) {
            if ($model->isConfirmed) {
                return '<div class="text-center"><span class="text-success">' . Yii::t('user', 'Confirmed') . '</span></div>';
            } else {
                return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-success btn-block',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                ]);
            }
        },
        'format' => 'raw',
        'visible' => Yii::$app->getModule('user')->enableConfirmation
    ],
    [
        'class' => \app\widgets\grid\UserManageColumn::className(),
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'balance',
                'content' => function (User $m) {
                    $content = Yii::$app->formatter->format($m->balance, 'currency');
                    $options = [];
                    $tag = 'span';
                    if ($m->balance == 0) {
                        Html::addCssClass($options, 'text-muted');
                    } else {
                        $tag = 'strong';
                        if ($m->balance > 0) {
                            //Html::addCssClass($options, 'text-success');
                        } else if ($m->balance < 0) {
                            Html::addCssClass($options, 'text-danger');
                        }
                    }
                    return Html::tag($tag, $content, $options);
                },
            ],
        ],
        'hAlign' => 'right',
    ],
/*
    [
        'label' => Yii::t('app', 'Actions'),
        'content' => function (User $m) {
            $buttons = ['update'];
            if ($m->isBlocked) {
                $bClass = 'btn-default';
                $bIcon = 'toggle-off';
                $bLabel = Yii::t('user', 'Unblock');
                $bConfirm = Yii::t('user', 'Are you sure you want to unblock this user?');
            } else {
                $bClass = 'btn-warning';
                $bIcon = 'toggle-on';
                $bLabel = Yii::t('user', 'Block');
                $bConfirm = Yii::t('user', 'Are you sure you want to block this user?');
            }
            $buttons[] = [
                'icon' => $bIcon,
                'label' => $bLabel,
                'url' => ['block', 'id' => $m->id],
                'options' => [
                    'class' => $bClass,
                    'data-pjax' => 0,
                    'data-method' => 'post',
                    'data-confirm' => $bConfirm,
                ],
            ];
            $buttons = UniHelper::actions2buttons(UniHelper::getUserActions($m, $buttons));
            return ButtonGroup::widget([
                'buttons' => $buttons,
                'options' => [
                    'class' => 'btn-group btn-group-xs btn-group-vertical'
                ],
            ]);
        },
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
*/
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    //'layout'  => "{items}\n{pager}",
    'columns' => $columns,
    'pjax' => true,
]);
