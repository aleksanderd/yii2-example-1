<?php

namespace app\models\variable\triggers;

use Yii;
use yii\helpers\ArrayHelper;

class WTMouseExit extends WTrigger {

    public function __construct($config = [])
    {

        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggers.mouseExit';
        }

//        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
//        ]);

        parent::__construct($config);

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
//            'action' => Yii::t('app', 'Scrolling down action'),
        ]);
    }

}
