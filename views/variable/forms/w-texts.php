<?php

use flyiing\helpers\Html;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model app\models\variable\WTexts */
/* @var $form app\widgets\ActiveForm */

//echo $this->render('w-texts', compact('model', 'form'));

$this->params['wrapperClass'] = 'gray-bg';

$items = [];
foreach ($model->languages as $l) {
    $m = $model->{$l};
    $items[] = [
        'label' => $model->getAttributeLabel($l),
        'content' => $this->render('w-texts-base', ['form' => $form, 'model' => $model->{$l}]),
    ];
}

$tabs = Tabs::widget([
//    'navType' => 'nav-pills',
    'items' => $items,
]);

echo Html::tag('div', $tabs, ['class' => 'tabs-container']);
