<?php

use app\helpers\ViewHelper;
use app\models\ClientRule;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientRuleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Rules');
$this->params['breadcrumbs'][] = Html::icon('client-rule') . $this->title;

$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['lines', 'sites'],
]);

$createOptions = [];
if ($user->getClientSites()->count() < 1 || $user->getClientLines()->count() < 1) {
    $createOptions['disabled'] = 'disabled';
} else if ($user->getClientRules()->count() < 1) {
    $createOptions['class'] = 'btn btn-primary btn-warning-dyn';
}
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add rule'),
        'options' => $createOptions,
    ],
]);


echo HintWidget::widget(['message' => '#ClientRuleIndex.hint']);
echo '<div class="client-rule-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$sites = \app\models\ClientSite::find();
$sites->andFilterWhere(['user_id' => $searchModel->user_id]);
$sites->andFilterWhere(['user_id' => $user->getSubjectUsers()->select('id')]);
$columns = [
    [
        'class' => \app\widgets\grid\SiteColumn::className(),
        'filterWidgetOptions' => [
            'data' => ArrayHelper::map($sites->all(), 'id', 'title'),
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filterTemplate' => '{id}',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'attributes' => [
            'id',
            [
                'attribute' => 'priority',
                'encodeLabel' => false,
                'label' => Html::icon('signal', '{i}'),
            ],
        ],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<strong>{title}</strong><br/><small>{description}</small>',
        'filterTemplate' => '{title}',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function (ClientRule $m) {
                    return Html::a($m->title, ['view', 'id' => $m->id], ['data-pjax' => 0]);
                },
            ],
            'description',
        ],
        'hAlign' => 'left',
    ],
    [
        'label' => Yii::t('app', 'Phone lines'),
        'content' => function(\app\models\ClientRule $model) {
            return $this->render('_lines_list', ['lines' => $model->getLines()]);
        }
    ],
    ['class' => 'flyiing\grid\ActionColumn'],
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
    'hover' => true,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-rule-index"
