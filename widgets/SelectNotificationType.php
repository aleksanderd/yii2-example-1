<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;

/**
 * Виджет селектора для выбора типа уведомления.
 */
class SelectNotificationType extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = [
                0 => Yii::t('app', 'Do not notify'),
                1 => Yii::t('app', 'E-mail only'),
                2 => Yii::t('app', 'SMS only'),
                3 => Yii::t('app', 'E-mail + SMS'),
            ];
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
