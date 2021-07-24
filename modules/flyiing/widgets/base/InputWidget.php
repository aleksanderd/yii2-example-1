<?php

namespace flyiing\widgets\base;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\base\InvalidConfigException;

class InputWidget extends \yii\widgets\InputWidget
{

    public function init()
    {
        parent::init();
        if ($this->hasModel()) {
            if (empty($this->name)) {
                $this->name = Html::getInputName($this->model, $this->attribute);
            }
            if (empty($this->value)) {
                $this->value = Html::getAttributeValue($this->model, $this->attribute);
            }
        }
    }

    public function run()
    {
        return Html::input(ArrayHelper::remove($this->options, 'type', 'text'),
            $this->name, $this->value, $this->options);
    }

}
