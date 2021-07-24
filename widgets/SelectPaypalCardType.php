<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;

/**
 * Виджет селектора типа кред.карты для пэйпала.
 */
class SelectPaypalCardType extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = [
                'visa' => Yii::t('app', 'VISA'),
                'mastercard' => Yii::t('app', 'MASTERCARD'),
                'maestro' => Yii::t('app', 'MAESTRO'),
                'amex' => Yii::t('app', 'AMEX'),
                'discover' => Yii::t('app', 'DISCOVER'),
            ];
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
