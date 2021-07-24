<?php

use app\models\Promocode;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use kartik\grid\GridView;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PromocodeSearch */
/* @var $inputForm app\models\PromocodeInputForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Promocodes');
$this->params['breadcrumbs'][] = $this->title;

if ($user->isAdmin) {
    $this->params['actions'] = UniHelper::getModelActions([
        'create' => [
            'label' => Yii::t('app', 'Add promocode'),
        ]
    ]);
}

echo HintWidget::widget(['message' => '#PromocodeIndex.hint']);
echo '<div class="promocode-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$form = ActiveForm::begin([
    'id' => 'promocode-input-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'method' => 'post',
    'action' => 'activate',
]);
echo $form->field($inputForm, 'code');
echo $form->buttons();
ActiveForm::end();

$columns = [
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'code',
                'content' => function (Promocode $m) {
                    return Html::a(Html::tag('strong', $m->code), ['view', 'id' => $m->id]);
                },
            ],
            [
                'attribute' => 'amount',
                'format' => 'currency',
            ],
        ],
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => 'expires_at',
                'value' => 'expiresText',
            ],
            [
                'attribute' => 'count',
                'value' => 'countText',
            ],
            [
                'attribute' => 'new_only',
                'value' => 'newOnlyText',
            ],
        ],
    ],
];
if ($user->isAdmin) {
    array_unshift($columns, 'user.username');
    $columns[] = ['class' => \flyiing\grid\ActionColumn::className()];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'columns' => $columns,
]);

echo '</div>' . PHP_EOL; // class="promocode-index"
