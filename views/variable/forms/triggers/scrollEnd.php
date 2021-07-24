<?php

use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\variable\triggers\WTScrollEnd */
/* @var $form app\widgets\ActiveForm */

echo Html::tag('h2', Yii::t('app', 'Scrolling down trigger'));
echo HintWidget::widget(['message' => '#WTScrollEnd.hint']) . '<hr/>';

echo $this->render('trigger', compact('form', 'model'));

echo $form->field($model, 'pct')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'postfix' => '%',
        'hide_min_max' => true,
        //'hide_from_to' => true,
        'grid' => true,
    ],
]);
