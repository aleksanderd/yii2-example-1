<?php

namespace app\models\variable;

use Yii;
use yii\base\DynamicModel;

/**
 * Модель ключа для переменной.
 *
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $page_id
 */
class VariableKey extends DynamicModel
{

    public function __construct($config = [])
    {
        $attributes = [
            'user_id',
            'site_id',
            'page_id',
        ];
        parent::__construct($attributes, $config);
        $this->addRule($attributes, 'safe');
    }

    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'page_id' => Yii::t('app', 'Page'),
        ];
    }

}