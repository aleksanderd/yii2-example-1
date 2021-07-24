<?php

namespace app\models\user;

use app\models\ClientLine;
use app\models\ClientSite;
use app\models\Promocode;
use app\models\ReferralStats;
use app\models\ReferralUrl;
use app\models\User;
use app\models\UserReferral;
use app\models\Variable;
use flyiing\helpers\FlashHelper;
use Yii;

class RegistrationForm extends \dektrium\user\models\RegistrationForm {

    public $referral;
    public $http_referrer;

    public $website;
    public $name;
    public $phone;

    /** @var \app\models\User|null */
    private $_partner;
    /** @var \app\models\Promocode|null */
    private $_promocode;
    /** @var \app\models\ReferralUrl|null */
    private $_referralUrl;

    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::createObject(User::className());
        $user->setScenario('register');
        $this->loadAttributes($user);

        if (!$user->register()) {
            FlashHelper::setFlash('error', Yii::t('app', 'User registration failed.'));
            return false;
        }
        $messages = [
            'success' => [Yii::t('app', 'Your account has been created and a message with further instructions has been sent to your email')],
            'info' => [],
            'warning' => [],
            'error' => [],
        ];
        if (isset($this->_partner)) {
            $referral = new UserReferral([
                'partner_id' => $this->_partner->id,
                'url_id' => $this->_referralUrl->id,
                'user_id' => $user->id,
                'scheme' => intval(Variable::sGet('u.settings.referralScheme', $this->_partner->id)),
            ]);
            if (!$referral->save()) {
                $messages['warning'][] = Yii::t('app', 'Creating referral link failed.');
            }
            ReferralStats::addValues([
                'user_id' => $this->_partner->id,
                'url_id' => $this->_referralUrl->id,
                'datetime' => time(),
                'registered' => 1,
            ]);
            if (!isset($this->_promocode)) {
                $this->_promocode = $this->_referralUrl->promocode;
            }
        }
        if (isset($this->_promocode)) {
            if ($activation = $this->_promocode->createActivation($user->id, $error)) {
                if ($activation->activate()) {
                    $messages['success'][] = Yii::t('app', 'Promocode activated.');
                } else {
                    $messages['warning'][] = Yii::t('app', 'Promocode activation failed.');
                }
            } else {
                $messages['warning'][] = $error;
            }
        }

        $profile = $user->profile;
        if (isset($this->name) && strlen($this->name) > 0) {
            $profile->name = $this->name;
        }
        if (isset($this->phone) && strlen($this->phone) > 0) {
            $profile->phone = $this->phone;
            $line = new ClientLine([
                'user_id' => $user->id,
                'title' => $this->phone,
                'info' => $this->phone,
            ]);
            if (!$line->save()) {
                $messages['warning'][] = Yii::t('app', 'Creating phone line failed.');
            }
        }
        if (isset($this->website) && strlen($this->website) > 0) {
            $profile->website = $this->website;
            $site = new ClientSite([
                'user_id' => $user->id,
                'title' => $this->website,
                'url' => $this->website,
            ]);
            if (!$site->save()) {
                $messages['warning'][] = Yii::t('app', 'Creating website failed.');
            }
        }
        if (!$profile->save()) {
            $messages['warning'][] = Yii::t('app', 'Profile updating failed.');
        }

        foreach ($messages as $level => $items) {
            foreach ($items as $m) {
                FlashHelper::setFlash($level, $m);
            }
        }
        return true;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['referral', 'http_referrer', 'website', 'name', 'phone'], 'string'],
            ['referral', 'trim'],
            ['referral', 'validateReferral']
        ]);
    }

    public function validateReferral()
    {
        if (!isset($this->referral)) {
            return true;
        }
        $partner = null;
        $ids = explode(ReferralUrl::ID_DELIMITER, $this->referral);
        $code = strtoupper(trim($this->referral));
        $partner_id = intval($ids[0]);
        if ($url = ReferralUrl::find()->where(['code' => $code])->one()) {
            /** @var ReferralUrl $url */
            $partner = $url->user;
            $partner_id = $partner->id;
        } else {
            if (!($partner_id > 0 && ($partner = User::findOne($partner_id)))) {
                /** @var \app\models\Promocode $promocode */
                $promocode = Promocode::findOne(['code' => $this->referral]);
                if (!($promocode && $promocode->isValid)) {
                    $this->addError('referral', Yii::t('app', 'Invalid partner ID or promocode "{code}".', compact('code')));
                    return false;
                }
                $this->_promocode = $promocode;
                $partner = $promocode->user;
            }
            if (count($ids) > 1) {
                $url = ReferralUrl::findOne(['user_id' => $partner_id, 'id' => intval($ids[1])]);
            }
        }
        if (!isset($url)) {
            if (!($url = ReferralUrl::defaultReferralUrl($partner_id))) {
                $this->addError('referral', Yii::t('app', 'Can not get partner referral url.'));
                return false;
            }
        }
        $this->_referralUrl = $url;
        $this->_partner = $partner;
        return true;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'referral' => Yii::t('app', 'Partner ID or promocode'),
            'http_referrer' => Yii::t('app', 'Url the user registered from'),
        ]);
    }

}
