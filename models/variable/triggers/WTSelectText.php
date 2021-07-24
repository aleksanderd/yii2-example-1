<?php

namespace app\models\variable\triggers;

use Yii;
use yii\helpers\ArrayHelper;

class WTSelectText extends WTrigger {

    public function __construct($config = [])
    {

        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggers.selectText';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'minCount',
        ]);

        parent::__construct($config);

        $this->addRule(['minCount'], 'integer', ['min' => 1, 'max' => 100]);

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
//            'action' => Yii::t('app', 'Scrolling down action'),
            'minCount' => Yii::t('app', 'Minimum of selections'),
        ]);
    }

}
