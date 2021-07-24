<?php

namespace app\models\variable\triggers;

use Yii;
use yii\helpers\ArrayHelper;

class WTPeriod extends WTrigger {

    public function __construct($config = [])
    {

        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggers.period';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'delay',
            'repeat',
        ]);

        parent::__construct($config);

        $this->addRule(['delay', 'repeat'], 'integer');

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            //action' => Yii::t('app', 'Periodical trigger action'),
            'delay' => Yii::t('app', 'After start timer'),
            'repeat' => Yii::t('app', 'Repeat timer'),
        ]);
    }

}
