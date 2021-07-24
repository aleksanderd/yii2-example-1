<?php

namespace flyiing\widgets\base;

class Widget extends \yii\base\Widget
{

    public function init()
    {
        parent::init();
        if (!isset($this->options['id']))
            $this->options['id'] = $this->getId();
    }

}
