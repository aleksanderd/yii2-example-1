<?php

namespace app\models\variable;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек про уведомления
 *
 */
class SNotify extends ANotify
{
    protected $_notifications;

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 's.notify';
        }
        $attributes = [
            'emailFrom',
            'smsFrom',
            'emailTo',
            'smsTo',
        ];
        $this->_notifications = [
            'userNew',
            'siteNew',
            'partnerNew',
            'payoutRequest',
            'widgetRemoved',
            'supportRequest',
        ];
        foreach ($this->_notifications as $n) {
            $attributes = array_merge($attributes, $this->notifyAttributes($n));
        }
        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), $attributes);
        parent::__construct($config);
        $this->addRule($attributes, 'string');
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        foreach ($this->_notifications as $n) {
            $labels = array_merge($labels, $this->notifyAttributeLabels($n));
        }
        return ArrayHelper::merge($labels, [
            'emailFrom' => Yii::t('app', 'Sender e-mail address'),
            'smsFrom' => Yii::t('app', 'Sender SMS number'),
            'emailTo' => Yii::t('app', 'Recipient e-mail address'),
            'smsTo' => Yii::t('app', 'Recipient SMS number'),
            'userNew' => Yii::t('app', 'New user notify'),
//            'userNewEmailSubject' => Yii::t('app', 'New user E-mail subject'),
//            'userNewEmailBody' => Yii::t('app', 'New user E-mail body'),
//            'userNewSmsBody' => Yii::t('app', 'New user SMS body'),
            'siteNew' => Yii::t('app', 'New website notify'),
//            'siteNewEmailSubject' => Yii::t('app', 'New website E-mail subject'),
//            'siteNewEmailBody' => Yii::t('app', 'New website E-mail body'),
//            'siteNewSmsBody' => Yii::t('app', 'New website SMS body'),
            'partnerNew' => Yii::t('app', 'New partner notify'),
            'payoutRequest' => Yii::t('app', 'Payout request notify'),
            'widgetRemoved' => Yii::t('app', 'Widget code removed'),
            'supportRequest' => Yii::t('app', 'Support request'),
        ]);
    }

}
