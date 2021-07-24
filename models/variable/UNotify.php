<?php

namespace app\models\variable;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек уведомлений пользователя
 */
class UNotify extends ANotify
{
    protected $_notifications;

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'u.notify';
        }
        $attributes = [
            'emailFrom',
            'copyTo',
            'smsFrom',
            'emailTo',
            'smsTo',
            'minBalanceValue',
        ];
        $this->_notifications = [
            'userNew',
            'siteNew',
            'siteNewInactive',
            'queryFail',
            'querySuccess',
            'queryUnpaid',
            'minBalance',
            'paymentNew',
            'tariffEnd',
            'tariffRenewFail',
            'payoutComplete',
            'payoutRejected',
            'supportReplied',
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
            'copyTo' => Yii::t('app', 'System copy e-mail address'),
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

            'siteNewInactive' => Yii::t('app', 'New inactive website notify'),
//            'siteNewInactiveEmailSubject' => Yii::t('app', 'New inactive website E-mail subject'),
//            'siteNewInactiveEmailBody' => Yii::t('app', 'New inactive website E-mail body'),
//            'siteNewInactiveSmsBody' => Yii::t('app', 'New inactive website SMS body'),

            'queryFail' => Yii::t('app', 'Query fail notify'),
//            'queryFailEmailSubject' => Yii::t('app', 'Query fail e-mail subject'),
//            'queryFailEmailBody' => Yii::t('app', 'Query fail query e-mail body'),
//            'queryFailSmsBody' => Yii::t('app', 'Query fail query SMS body'),

            'querySuccess' => Yii::t('app', 'Query success notify'),
//            'querySuccessEmailSubject' => Yii::t('app', 'Query success e-mail subject'),
//            'querySuccessEmailBody' => Yii::t('app', 'Query success e-mail body'),
//            'querySuccessSmsBody' => Yii::t('app', 'Query success SMS body'),

            'queryUnpaid' => Yii::t('app', 'Unpaid query notify'),
//            'queryUnpaidEmailSubject' => Yii::t('app', 'Unpaid query e-mail subject'),
//            'queryUnpaidEmailBody' => Yii::t('app', 'Unpaid query e-mail body'),
//            'queryUnpaidSmsBody' => Yii::t('app', 'Unpaid query SMS body'),

            'minBalance' => Yii::t('app', 'Min balance notify'),
            'minBalanceValue' => Yii::t('app', 'Min balance value'),
//            'minBalanceEmailSubject' => Yii::t('app', 'Min balance e-mail subject'),
//            'minBalanceEmailBody' => Yii::t('app', 'Min balance e-mail body'),
//            'minBalanceSmsBody' => Yii::t('app', 'Min balance SMS body'),

            'paymentNew' => Yii::t('app', 'Payment notify'),
//            'paymentNewEmailSubject' => Yii::t('app', 'Payment e-mail subject'),
//            'paymentNewEmailBody' => Yii::t('app', 'Payment e-mail body'),
//            'paymentNewSmsBody' => Yii::t('app', 'Payment SMS body'),

            'tariffEnd' => Yii::t('app', 'Tariff end notify'),
//            'tariffEndEmailSubject' => Yii::t('app', 'Tariff end e-mail subject'),
//            'tariffEndEmailBody' => Yii::t('app', 'Tariff end e-mail body'),
//            'tariffEndSmsBody' => Yii::t('app', 'Tariff end SMS body'),

            'tariffRenewFail' => Yii::t('app', 'Tariff renew fail'),
//            'tariffRenewFailEmailSubject' => Yii::t('app', 'Tariff renew fail e-mail subject'),
//            'tariffRenewFailEmailBody' => Yii::t('app', 'Tariff renew fail e-mail body'),
//            'tariffRenewFailSmsBody' => Yii::t('app', 'Tariff renew fail SMS body'),

            'payoutComplete' => Yii::t('app', 'Payout complete notify'),
            'payoutReject' => Yii::t('app', 'Payout rejected notify'),
            'supportReplied' => Yii::t('app', 'Support replied notify'),

        ]);
    }

    public function adminAttributes()
    {
        return [
            'emailFrom',
            'copyTo',
            'smsFrom',

            'userNew',
            'userNewEmailSubject',
            'userNewEmailBody',
            'userNewSmsBody',

            'siteNew',
            'siteNewEmailSubject',
            'siteNewEmailBody',
            'siteNewSmsBody',

            'siteNewInactive',
            'siteNewInactiveEmailSubject',
            'siteNewInactiveEmailBody',
            'siteNewInactiveSmsBody',

            'querySuccessSmsBody',
            'queryFailSmsBody',

            'queryUnpaid',
            'queryUnpaidEmailSubject',
            'queryUnpaidEmailBody',
            'queryUnpaidSmsBody',

            'minBalanceEmailSubject',
            'minBalanceEmailBody',
            'minBalanceSmsBody',

            'paymentNewEmailSubject',
            'paymentNewEmailBody',
            'paymentNewSmsBody',

            'tariffEndEmailSubject',
            'tariffEndEmailBody',
            'tariffEndSmsBody',

//            'tariffRenewFail',
            'tariffRenewFailEmailSubject',
            'tariffRenewFailEmailBody',
            'tariffRenewFailSmsBody',

            'payoutCompleteEmailSubject',
            'payoutCompleteEmailBody',
            'payoutCompleteSmsBody',

            'payoutRejectedEmailSubject',
            'payoutRejectedEmailBody',
            'payoutRejectedSmsBody',
        ];
    }

}
