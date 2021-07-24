<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель системных настроек
 *
 */
class SSettings extends VariableModel
{
    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 's.settings';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'title',
            'url',
            'baseUrl',

            'supportEmail',
            'salesEmail',

            'trialTariff',
            'trialActivation',

            'timeoutNewUser',
            'timeoutActiveUser',
            'timeoutInactiveUser',

            'referralFixedFirst',
            'referralPercentFirst',
            'referralPercent',
            'referralTimeLimit',
            'referralAgreement',
            'referralAgreementVersion',

            'referralGiftMax',
            'referralGiftPaymentsRequired',

            'payoutMin',
            'payoutMax',
            'payoutInterval',
        ]);

        parent::__construct($config);

        $this->addRule(['supportEmail', 'salesEmail'], 'string');
        $this->addRule(['title', 'url', 'baseUrl'], 'string');

        $this->addRule(['trialTariff', 'trialActivation'], 'integer');

        $this->addRule([
            'referralFixedFirst',
            'payoutMin',
            'payoutMax',
            'referralGiftMax',
            'referralGiftPaymentsRequired'
        ], 'number');

        $this->addRule(['referralPercentFirst', 'referralPercent'], 'integer');
        $this->addRule(['referralTimeLimit', 'payoutInterval'], 'integer', ['min' => 0, 'max' => 9999999]);
        $this->addRule(['referralAgreement', 'referralAgreementVersion'], 'string');
        $this->addRule(['timeoutNewUser', 'timeoutActiveUser', 'timeoutInactiveUser',], 'integer');
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'title' => Yii::t('app', 'Application title'),
            'url' => Yii::t('app', 'Url'),
            'baseUrl' => Yii::t('app', 'Base url'),

            'supportEmail' => Yii::t('app', 'Support e-mail'),
            'salesEmail' => Yii::t('app', 'Sales e-mail'),

            'trialTariff' => Yii::t('app', 'Trial tariff'),
            'trialActivation' => Yii::t('app', 'Trial activation'),

            'timeoutNewUser' => Yii::t('app', 'New user timeout'),
            'timeoutActiveUser' => Yii::t('app', 'Active user timeout'),
            'timeoutInactiveUser' => Yii::t('app', 'Inactive user timeout'),

            'referralFixedFirst' => Yii::t('app', 'First payment fixed bonus'),
            'referralPercentFirst' => Yii::t('app', 'First payment percent'),
            'referralPercent' => Yii::t('app', 'Lifetime percent'),
            'referralTimeLimit' => Yii::t('app', 'Referral time limit'),
            'referralAgreement' => Yii::t('app', 'Referral agreement'),
            'referralAgreementVersion' => Yii::t('app', 'Referral agreement version'),
            'referralGiftMax' => Yii::t('app', 'Maximal amount of referral gift'),
            'referralGiftPaymentsRequired' => Yii::t('app', 'Payments required for gift activation'),

            'payoutMin' => Yii::t('app', 'Minimal payout amount'),
            'payoutMax' => Yii::t('app', 'Maximal payout amount'),
            'payoutInterval' => Yii::t('app', 'Minimal payout interval'),

        ]);
    }

}
