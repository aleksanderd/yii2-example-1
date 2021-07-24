<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/**
 * Виджет селектора для выбора языка.
 */
class SelectLanguage extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = [
                'en' => 'English',
                'ru' => 'Russian'
            ];
            if (ArrayHelper::remove($config, 'autoItem', false)) {
                $config['data'] = array_merge(['auto' => 'Auto'], $config['data']);
            }
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
