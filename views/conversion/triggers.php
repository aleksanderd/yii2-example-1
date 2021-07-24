<?php

use app\models\Conversion;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\ConversionSearch */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Triggers conversion');
$this->params['breadcrumbs'][] = $this->title;

echo HintWidget::widget(['message' => '#Conversion.triggers.hint']);
echo '<div class="conversion-triggers">' . PHP_EOL;
echo AlertFlash::widget();

echo $this->render('_search', compact('searchModel'));

$trCol = function ($trName) {
    if ($trName != 'manual') {
        $trName = 'tr_' . $trName;
    }
    return [
        'class' => \flyiing\grid\DataColumn::className(),
        'attributes' => [
            [
                'attribute' => $trName . '_wins',
                'content' => function (Conversion $m) use ($trName) {
                    $val = ArrayHelper::getValue($m, $trName . '_wins', 0);
                    if ($val > 0) {
                        $tag = 'strong';
                        $class = 'text-info';
                    } else {
                        $tag = 'small';
                        $class = 'text-muted';
                    }
                    return Html::tag($tag, $val, ['class' => $class]);
                }
            ],
            [
                'attribute' => $trName . '_queries',
                'content' => function (Conversion $m) use ($trName) {
                    $val = ArrayHelper::getValue($m, $trName . '_queries', 0);
                    if ($val > 0) {
                        $tag = 'strong';
                        $class = 'text-success huge-bold';
                    } else {
                        $tag = 'small';
                        $class = 'text-muted';
                    }
                    return Html::tag($tag, $val, ['class' => $class]);
                }
            ],
        ],
        'hAlign' => 'center',
    ];
};

$columns = [];
$trs = ['scrollEnd', 'selectText', 'mouseExit', 'period', 'total', 'manual'];
foreach ($trs as $tr) {
    $columns[] = $trCol($tr);
}

echo $this->render('_conversion_grid', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'options' => [
        'columns' => $columns,
    ],
]);

echo '</div>';
