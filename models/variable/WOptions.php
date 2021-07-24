<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек основных для клиентского виджета
 *
 * @property string $language
 * @property WButtonOptions $buttonOptions
 * @property WModalOptions $modalOptions
 * @property WTriggersOptions $triggersOptions
 */
class WOptions extends VariableModel {

    public function __construct($config = [])
    {

        $this->_classes = array_merge($this->_classes, [
            'buttonOptions' => WButtonOptions::className(),
            'modalOptions' => WModalOptions::className(),
            'triggersOptions' => WTriggersOptions::className(),
        ]);

        if (!isset($config['name'])) {
            $config['name'] = 'w.options';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'language',

            'mode',
            'startDelay',
            'restoreInfo',
            'defaultPrefix',
            'mobileMode',
            'noRuleMode',

            'deferredTries',

        ]);

        parent::__construct($config);

        $this->addRule('language', 'string', ['min' => 2, 'max' => 5]);
        $this->addRule(['restoreInfo', 'startDelay', 'intervalMin', 'deferredTries'], 'integer', ['min' => 0]);
        $this->addRule('defaultPrefix', 'string', ['min' => 2, 'max' => 5]);
        $this->addRule(['mode', 'mobileMode', 'noRuleMode'], 'string');

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'language' => Yii::t('app', 'Language'),
            'mode' => Yii::t('app', 'Widget mode'),
            'startDelay' => Yii::t('app', 'Start delay'),
            'restoreInfo' => Yii::t('app', 'Restore call info'),
            'defaultPrefix' => Yii::t('app', 'Default prefix'),
            'mobileMode' => Yii::t('app', 'Mobile devices'),
            'noRuleMode' => Yii::t('app', 'No rule mode'),
            'deferredTries' => Yii::t('app', 'Deferred tries'),
        ]);
    }

    public function getValues()
    {
        $result = parent::getValues();
        $result['baseUrl'] = Yii::$app->request->hostInfo . '/';
        return $result;
    }

}
