<?php

/* @var $this yii\web\View */
use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use flyiing\helpers\Html;
use kartik\touchspin\TouchSpin;

/* @var $model app\models\variable\WTriggersOptions */
/* @var $form app\widgets\ActiveForm */

$view = $this;
$renderTrigger = function($trigger) use ($view, $form, $model) {
    if (!isset($model->{$trigger})) {
        return Yii::t('app', 'Unknown trigger [{n}]', ['n' => $trigger]);
    }
    return $view->render('triggers/' . $trigger, [
        'form' => $form,
        'model' => $model->{$trigger},
    ]);// . '<hr>';
};

echo '<div class="panel-body">';
echo HintWidget::widget(['message' => '#WTriggersOptions.hint']) . '<hr/>';

echo Html::tag('h2', Yii::t('app', 'General triggers options'));
echo HintWidget::widget(['message' => '#WTriggersOptions.general.hint']) . '<hr/>';

echo $form->field($model, 'startInterval')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);

echo $form->field($model, 'minInterval')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 999999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);

echo $form->field($model, 'countLimit')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 60,
        'step' => 1,
        'hide_min_max' => true,
        //'hide_from_to' => true,
        'grid' => true,
    ],
]);

echo $renderTrigger('scrollEnd');
echo $renderTrigger('selectText');
echo $renderTrigger('mouseExit');
echo $renderTrigger('period');

echo '</div>';
