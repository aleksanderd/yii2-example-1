<?php

namespace app\models\variable\triggers;

use Yii;
use yii\helpers\ArrayHelper;

class WTScrollEnd extends WTrigger {

    public function __construct($config = [])
    {

        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggers.scrollEnd';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'pct',
        ]);

        parent::__construct($config);

        $this->addRule(['pct'], 'integer', ['min' => 0, 'max' => 100]);

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
//            'action' => Yii::t('app', 'Scrolling down action'),
            'pct' => Yii::t('app', 'Scrolling page percent'),
        ]);
    }

}
