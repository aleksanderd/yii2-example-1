<?php

use app\models\Notification;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\NotificationSearch */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Notifications');
$this->params['breadcrumbs'][] = $this->title;

echo HintWidget::widget(['message' => '#NotificationIndex.hint']);
echo '<div class="notification-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'content' => function (Notification $m) {
            return Html::a($m->id, ['view', 'id' => $m->id], ['data-pjax' => 0]);
        },
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'at',
                'format' => 'datetime',
            ],
        ],
        'hAlign' => 'center',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'type',
                'content' => function (Notification $m) {
                    return ArrayHelper::getValue($m->typeLabels(), $m->type, '-');
                }
            ],
            'status',
        ],
        'hAlign' => 'center',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => Html::activeTextInput($searchModel, 'to_from', [
            'class' => 'form-control compact',
            'placeholder' => $searchModel->attributeLabels()['to_from'],
        ]),
        'attributes' => [
            [
                'attribute' => 'to',
            ],
            [
                'attribute' => 'from',
            ],
        ],
        'hAlign' => 'left',
    ],
    'title:ntext',
];

if ($user->isAdmin) {
    $columns = array_merge([
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
    'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="locale-text-index"
