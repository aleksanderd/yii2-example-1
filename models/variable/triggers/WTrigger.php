<?php

namespace app\models\variable\triggers;

use Yii;
use app\models\VariableModel;
use yii\helpers\ArrayHelper;

class WTrigger extends VariableModel {

    public function __construct($config = [])
    {

        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggers.trigger';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'action',
            'actionDelay',
            'countLimit',
        ]);

        parent::__construct($config);

        $this->addRule(['action'], 'string');
        $this->addRule(['actionDelay', 'countLimit'], 'integer', ['min' => 0]);

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'action' => Yii::t('app', 'Trigger action'),
            'actionDelay' => Yii::t('app', 'Trigger action delay'),
            'countLimit' => Yii::t('app', 'Trigger count limit'),
        ]);
    }

}
