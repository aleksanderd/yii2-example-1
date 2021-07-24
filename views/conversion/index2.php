<?php

use app\models\Conversion;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\ConversionSearch */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Website conversion increase');
$this->params['breadcrumbs'][] = $this->title;

$css = <<<CSS

.division {
    display: inline-block;
    text-align: center;
}

.division hr {
    margin: 0 auto;
    border-color: rgba(0, 0, 0, 0.382);
}

CSS;
$this->registerCss($css);

echo HintWidget::widget(['message' => '#Conversion.hint']);
echo '<div class="conversion-index">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', compact('searchModel'));

$columns = [

    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<div class="division big-bold">{wins}<hr>{visits_unique}</div>',
        'attributes' => [
            'wins',
            'visits_unique',
        ],
        'hAlign' => 'right',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'wins_per_visit',
        'label' => Yii::t('app', 'Percent') . ' %',
        'content' => function(Conversion $m) {
            return Html::tag('span', sprintf(' =&nbsp;%.01f%%', $m->wins_per_visit), ['class' => 'huge-bold']);
        },
        'hAlign' => 'left',
        'vAlign' => 'middle',
    ],

    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<div class="division big-bold">{queries}<hr>{wins}</div>',
        'attributes' => [
            [
                'attribute' => 'queries',
                'label' => Yii::t('app', 'Queries'),
            ],
            [
                'attribute' => 'wins',
                'label' => Yii::t('app', 'Shows'),
            ],
        ],
        'hAlign' => 'right',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'queries_per_wins',
        'label' => Yii::t('app', 'Percent') . ' %',
        'content' => function(Conversion $m) {
            return Html::tag('span', sprintf(' =&nbsp;%.01f%%', $m->queries_per_wins), ['class' => 'huge-bold']);
        },
        'hAlign' => 'left',
        'vAlign' => 'middle',
    ],

    [
        'class' => \flyiing\grid\DataColumn::className(),
        'template' => '<div class="division big-bold">{queries_success}<hr>{queries}</div>',
        'attributes' => [
            [
                'attribute' => 'queries_success',
                'label' => Yii::t('app', 'Success'),
            ],
            [
                'attribute' => 'queries',
                'label' => Yii::t('app', 'Queries'),
            ],
        ],
        'hAlign' => 'right',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'success_per_query',
        'label' => Yii::t('app', 'Percent') . ' %',
        'content' => function(Conversion $m) {
            return Html::tag('span', sprintf(' =&nbsp;%.01f%%', $m->success_per_query), ['class' => 'huge-bold']);
        },
        'hAlign' => 'left',
        'vAlign' => 'middle',
    ],

];

echo $this->render('_conversion_grid', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'options' => [
        'columns' => $columns,
    ],
]);

echo '</div>';
