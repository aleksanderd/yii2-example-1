<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;

/**
 * Виджет селектора для выбора действия по триггеру.
 */
class SelectWidgetAction extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = [
                'ignore' => Yii::t('app', 'Ignore'),
                'showModal' => Yii::t('app', 'Show modal window'),
            ];
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
