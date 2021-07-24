<?php

use app\helpers\ViewHelper;
use app\models\ClientPage;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientPageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Pages');
$this->params['breadcrumbs'][] = Html::icon('client-page') . $this->title;
$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['lines', 'rules', 'sites'],
]);
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add page'),
        'url' => ['create', 'site_id' => $searchModel->site_id],
    ]
]);

echo HintWidget::widget(['message' => '#ClientPageIndex.hint']);
echo '<div class="client-page-index">' . PHP_EOL;
echo AlertFlash::widget();

//echo $this->render('_search', ['model' => $searchModel]);

$sites = \app\models\ClientSite::find();
$sites->andFilterWhere(['user_id' => $searchModel->user_id]);
$sites->andFilterWhere(['user_id' => $user->getSubjectUsers()->select('id')]);

$columns = [
    [
        'attribute' => 'id',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'hAlign' => 'right',
    ],
    [
        'class' => \app\widgets\grid\SiteColumn::className(),
        'filterWidgetOptions' => [
            'data' => ArrayHelper::map($sites->all(), 'id', 'title'),
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
        'template' => '<strong>{title}</strong><br/><small>{pattern}</small>',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function (ClientPage $m) {
                    return Html::a($m->title, ['view', 'id' => $m->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute' => 'pattern',
            ],
        ],
    ],
//    'typeLabel',
//    ['class' => 'flyiing\grid\ActionColumn'],
];

if (count($user->subjectUsers) > 1) {
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

echo '</div>' . PHP_EOL; // class="client-page-index"
