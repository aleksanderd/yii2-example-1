<?php

namespace app\models\variable;

use app\models\VariableModel;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек для пользователя системы
 *
 * @property string $language Язык
 */
class USettings extends VariableModel
{
    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'u.settings';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'language',
            'timezone',
            'pageAnimation',
            'referralScheme',
        ]);

        parent::__construct($config);

        $this->addRule('language', 'string', ['min' => 2, 'max' => 5]);
        $this->addRule('timezone', 'string', ['min' => 2,]);
        $this->addRule('pageAnimation', 'string', ['min' => 2,]);
        $this->addRule('referralScheme', 'integer', ['min' => 0, 'max' => 1]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'language' => Yii::t('app', 'Language'),
            'timezone' => Yii::t('app', 'Time zone'),
            'pageAnimation' => Yii::t('app', 'Page animation'),
            'referralScheme' => Yii::t('app', 'Referral scheme'),
        ]);
    }

}
