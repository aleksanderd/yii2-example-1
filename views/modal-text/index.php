<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ModalTextSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Modal Texts');
$this->params['breadcrumbs'][] = Html::icon('modal-text') . $this->title;

$actions = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add modal text'),
    ]
]);

if ($user->isAdmin) {
    $actions = array_merge($actions, [
        'stats' => [
            'label' => Yii::t('app', 'Statistics'),
            'url' => ['/modal-text/stats'],
            'options' => ['class' => 'btn-info']
        ],
    ]);
}
$this->params['actions'] = $actions;

echo '<div class="modal-text-index">' . PHP_EOL;

echo AlertFlash::widget();
// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            'id',
            'language',
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{title}</strong><br/>{m_title}<br/><small>{m_submit}</small>',
        'attributes' => [
            'title',
            'm_title',
            'm_submit',
        ],
    ],

    'm_description:ntext',

    ['class' => 'flyiing\grid\ActionColumn'],
];

if ($user->isAdmin) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
        ],
    ], $columns);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="modal-text-index"
