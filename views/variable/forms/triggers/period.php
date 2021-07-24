<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use kartik\touchspin\TouchSpin;

/* @var $this yii\web\View */
/* @var $model app\models\variable\triggers\WTPeriod */
/* @var $form app\widgets\ActiveForm */

echo Html::tag('h2', Yii::t('app', 'Periodical trigger'));
echo HintWidget::widget(['message' => '#WTPeriod.hint']) . '<hr/>';

echo $this->render('trigger', compact('form', 'model'));

$sOpts = [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
];

echo $form->field($model, 'delay')->widget(TouchSpin::className(), $sOpts);
echo $form->field($model, 'repeat')->widget(TouchSpin::className(), $sOpts);
