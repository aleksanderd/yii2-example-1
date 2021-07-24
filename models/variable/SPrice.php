<?php

namespace app\models\variable;

use app\models\VariableModel;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель цен
 *
 */
class SPrice extends VariableModel
{
    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 's.price';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'callMinute',
            'sms',
            'email',
        ]);

        parent::__construct($config);

        $this->addRule('callMinute', 'number');
        $this->addRule('sms', 'number');
        $this->addRule('email', 'number');
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'callMinute' => Yii::t('app', 'Call minute price'),
            'sms' => Yii::t('app', 'SMS price'),
            'email' => Yii::t('app', 'E-mail price'),
        ]);
    }

}
