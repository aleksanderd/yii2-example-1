<?php

use app\helpers\ViewHelper;
use app\models\ClientLine;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use flyiing\helpers\UniHelper;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientLineSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Phone lines');
$this->params['breadcrumbs'][] = Html::icon('client-line') . $this->title;
$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['rules', 'sites'],
]);
$createOptions = [];
if ($user->getClientSites()->count() < 1) {
//    $createOptions['disabled'] = 'disabled';
} else if ($user->getClientLines()->count() < 1) {
    $createOptions['class'] = 'btn btn-primary btn-warning-dyn';
}
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add phone line'),
        'options' => $createOptions,
    ],
]);

echo HintWidget::widget(['message' => '#ClientLineIndex.hint']);
echo '<div class="client-line-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => Html::activeTextInput($searchModel, 'itd', [
            'class' => 'form-control',
            'placeholder' => $searchModel->attributeLabels()['itd'],
        ]),
        'template' => '<strong>{title}</strong><br><small>{description}</small>',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function (ClientLine $m) {
                    return Html::a($m->title, ['view', 'id' => $m->id], ['data-pjax' => 0]);
                },
            ],
            'description'
        ],
    ],
    [
        'attribute' => 'info',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'noWrap' => true,
        'content' => function($model) {
            return Html::tag('h4', Html::icon('phone') . $model->info);
        },
    ],
    //['class' => 'flyiing\grid\ActionColumn'],
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
    'hover' => true,
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-line-index"
