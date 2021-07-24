<?php

namespace app\models\variable;

use Yii;
use app\models\VariableModel;
use app\models\variable\triggers\WTPeriod;
use app\models\variable\triggers\WTScrollEnd;
use app\models\variable\triggers\WTMouseExit;
use app\models\variable\triggers\WTSelectText;
use yii\helpers\ArrayHelper;

class WTriggersOptions extends VariableModel {

    public function __construct($config = [])
    {
        $this->_classes = array_merge($this->_classes, [
            'period' => WTPeriod::className(),
            'scrollEnd' => WTScrollEnd::className(),
            'selectText' => WTSelectText::className(),
            'mouseExit' => WTMouseExit::className(),
        ]);


        if (!isset($config['name'])) {
            $config['name'] = 'w.options.triggersOptions';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'startInterval',
            'minInterval',
            'countLimit',
        ]);

        parent::__construct($config);

        $this->addRule(['startInterval', 'minInterval', 'countLimit'], 'integer', ['min' => 0]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'startInterval' => Yii::t('app', 'Start interval'),
            'minInterval' => Yii::t('app', 'Minimal interval'),
            'countLimit' => Yii::t('app', 'Overall count limit'),
        ]);
    }

}
