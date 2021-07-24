<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;

/**
 * Виджет селектора для выбора префикса телефонного номера.
 */
class SelectPhonePrefix extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $data = [];
            foreach (['+1', '+7', '+0', '+44'] as $prefix) {
                $data[$prefix] = Yii::t('app', $prefix);
            }
            $config['data'] = $data;
            if (!isset($config['hideSearch'])) {
                $config['hideSearch'] = true;
            }
        }
        parent::__construct($config);
    }

}
