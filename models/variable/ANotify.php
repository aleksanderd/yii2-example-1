<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;

class ANotify extends VariableModel {

    public static function notifyAttributes($prefix)
    {
        return [
            $prefix,
            $prefix . 'EmailSubject',
            $prefix . 'EmailBody',
            $prefix . 'SmsBody',
        ];
    }

    public function addNotifyRules($prefix)
    {
        $this->addRule($prefix, 'integer');
        $this->addRule([
            $prefix . 'EmailSubject',
            $prefix . 'EmailBody',
            $prefix . 'SmsBody',
        ], 'string');
    }

    public function notifyAttributeLabels($prefix)
    {
        return [
            $prefix . 'EmailSubject' => Yii::t('app', 'E-mail subject'),
            $prefix . 'EmailBody' => Yii::t('app', 'E-mail body'),
            $prefix . 'SmsBody' => Yii::t('app', 'SMS body'),
        ];
    }
}