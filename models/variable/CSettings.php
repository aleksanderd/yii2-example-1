<?php

namespace app\models\variable;

use app\models\VariableModel;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек для системы совершения звонков
 *
 */
class CSettings extends VariableModel
{
    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'c.settings';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'line',
            'voiceType',
            'mClientCallFailed',
            'mClientCallFailedAudio',
            'mIncomingCall',
            'mIncomingCallAudio',
        ]);

        parent::__construct($config);

        $this->addRule([
            'line',
            'voiceType',
            'mClientCallFailed',
            'mClientCallFailedAudio',
            'mIncomingCall',
            'mIncomingCallAudio',
        ], 'string');
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'line' => Yii::t('app', 'VI Line'),
            'voiceType' => Yii::t('app', 'Voice type'),
            'mClientCallFailed' => Yii::t('app', 'Client call failed'),
            'mClientCallFailedAudio' => Yii::t('app', 'Audio file'),
            'mIncomingCall' => Yii::t('app', 'Incoming call'),
            'mIncomingCallAudio' => Yii::t('app', 'Audio file'),
        ]);
    }

    public function adminAttributes()
    {
        return ['line'];
    }

}
