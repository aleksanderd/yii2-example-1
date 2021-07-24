<?php

namespace flyiing\translation;

class Module extends \yii\base\Module
{

    public $languages;

    public function init()
    {
        if (!isset($this->languages)) {
            $this->languages = ['en', 'ru'];
        }
        parent::init();
    }

}