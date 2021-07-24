<?php

use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\variable\triggers\WTSelectText */
/* @var $form app\widgets\ActiveForm */

echo Html::tag('h2', Yii::t('app', 'Text selection trigger'));
echo HintWidget::widget(['message' => '#WTSelectText.hint']) . '<hr/>';

echo $this->render('trigger', compact('form', 'model'));

echo $form->field($model, 'minCount')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 1,
        'max' => 20,
        'step' => 1,
        'hide_min_max' => true,
        //'hide_from_to' => true,
        //'grid' => true,
    ],
]);
