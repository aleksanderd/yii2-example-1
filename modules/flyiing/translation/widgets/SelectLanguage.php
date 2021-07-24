<?php

namespace flyiing\translation\widgets;

use kartik\select2\Select2;

class SelectLanguage extends Select2 {

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = [];
            /** @var \flyiing\translation\Module $tModule */
            $tModule = \Yii::$app->getModule('translation');
            foreach ($tModule->languages as $l) {
                $config['data'][$l] = $l;
            }
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
