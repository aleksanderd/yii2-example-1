<?php

namespace app\widgets;

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\helpers\Inflector;

class ActiveForm extends \flyiing\widgets\ActiveForm {

    public $fieldClass = 'app\widgets\ActiveField';

    public $fieldConfig = [
        // скопировано из yii\bootstrap\ActiveField
        'horizontalCssClasses' => [
            'offset' => 'col-md-offset-3 col-sm-offset-4',
            'label' => 'col-md-3 col-sm-4',
            'wrapper' => 'col-md-5 col-sm-8',
            'error' => '',
            'hint' => 'col-md-4 col-sm-12',
        ],
    ];

    public $hint = true;

    public function init()
    {
        if ($this->hint === true) {
            $hint = HintWidget::widget(['message' => '#' . Inflector::id2camel($this->id) . '.hint']);
            echo Html::tag('div', $hint, ['class' => 'form-hint']);
        }
        parent::init();
    }

}
