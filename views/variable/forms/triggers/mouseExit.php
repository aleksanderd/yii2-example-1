<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\variable\triggers\WTMouseExit */
/* @var $form app\widgets\ActiveForm */

echo Html::tag('h2', Yii::t('app', 'Mouse exit trigger'));
echo HintWidget::widget(['message' => '#WTMouseExit.hint']) . '<hr/>';

echo $this->render('trigger', compact('form', 'model'));
