<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\query\UserReferralQuery;

/**
 * This is the model class for table "{{%user_referral}}".
 *
 * @property integer $partner_id
 * @property integer $user_id
 * @property integer $url_id
 * @property integer $scheme
 * @property integer $status
 * @property string $paid
 * @property integer $p_access
 *
 * @property float $referralPaid
 * @property string $statusText
 * @property string $schemeText
 * @property string $schemeTextPct
 * @property bool $isActive
 * @property bool $isPaid
 * @property string $title
 * @property User $partner
 * @property ReferralUrl $url
 * @property User $user
 * @property UserReferralTransaction[] $transactions
 */
class UserReferral extends \yii\db\ActiveRecord
{

    const SCHEME_CHOICE_REQUIRED = 0;
    const SCHEME_FIRST_ONLY = 1;
    const SCHEME_LIFETIME_LIMITED = 10;
//    const SCHEME_LIFETIME = 100;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 100;
    const STATUS_ACTIVE_PAID = 200;
    const STATUS_FINISHED = 1000;

    const ACCESS_DENY = 0;
    const ACCESS_ALLOW = 100;

    /**
     * @return array
     */
    public static function statusLabels()
    {
        return [
            static::STATUS_INACTIVE => Yii::t('app', 'Inactive referral'),
            static::STATUS_ACTIVE => Yii::t('app', 'Active referral'),
            static::STATUS_ACTIVE_PAID => Yii::t('app', 'Paid referral'),
            static::STATUS_FINISHED => Yii::t('app', 'Finished referral'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return ArrayHelper::getValue($this->statusLabels(), $this->status, Yii::t('app', 'Unknown status'));
    }

    public static function schemeLabels()
    {
        return [
            static::SCHEME_CHOICE_REQUIRED => Yii::t('app', 'Select later'),
            static::SCHEME_FIRST_ONLY => Yii::t('app', 'First payment percent'),
            static::SCHEME_LIFETIME_LIMITED => Yii::t('app', 'Lifetime percent'),
        ];
    }

    public function schemeLabelsPct()
    {
        return [
            static::SCHEME_CHOICE_REQUIRED => Yii::t('app', 'Select later'),
            static::SCHEME_FIRST_ONLY => Yii::t('app', 'First {pct}%',
                ['pct' => intval(Variable::sGet('s.settings.referralPercentFirst', $this->partner_id))]),
            static::SCHEME_LIFETIME_LIMITED => Yii::t('app', 'Lifetime {pct}%',
                ['pct' => intval(Variable::sGet('s.settings.referralPercent', $this->partner_id))]),
        ];
    }

    public static function accessLabels()
    {
        return [
            static::ACCESS_DENY => Yii::t('app', 'Deny'),
            static::ACCESS_ALLOW => Yii::t('app', 'Allow'),
        ];
    }

    public function __get($name)
    {
        if ($name == 'paid' && ($this->status === static::STATUS_INACTIVE || $this->scheme === static::SCHEME_CHOICE_REQUIRED)) {
            return null;
        }
        return parent::__get($name);
    }

    /**
     * @return float
     */
    public function getReferralPaid()
    {
        return $this->user->getPayments()->where(['status' => Payment::STATUS_COMPLETED])->sum('amount');
    }

    /**
     * @return string
     */
    public function getSchemeText()
    {
        return ArrayHelper::getValue($this->schemeLabels(), $this->scheme, Yii::t('app', 'Unknown lifetime'));
    }

    /**
     * @return string
     */
    public function getSchemeTextPct()
    {
        return ArrayHelper::getValue($this->schemeLabelsPct(), $this->scheme, Yii::t('app', 'Unknown lifetime'));
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->status >= static::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function getIsPaid()
    {
        return $this->status >= static::STATUS_ACTIVE_PAID;
    }

    public function getTitle()
    {
        return '[' . $this->partner->username .'] '. $this->user->username;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_referral}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'user_id'], 'required'],
            [['partner_id', 'user_id', 'url_id', 'scheme', 'status'], 'integer'],
            [['paid'], 'number'],
            [['user_id'], 'unique'],
            [
                ['partner_id', 'user_id'],
                function() {
                    if ($this->partner_id == $this->user_id) {
                        $this->addError('user_id', Yii::t('app', 'Select another user.'));
                    }
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'partner_id' => Yii::t('app', 'Partner user'),
            'partner.username' => Yii::t('app', 'Partner user'),
            'user_id' => Yii::t('app', 'Referral user'),
            'user.username' => Yii::t('app', 'Referral user'),
            'url_id' => Yii::t('app', 'Referral url'),
            'url.title' => Yii::t('app', 'Referral url'),
            'scheme' => Yii::t('app', 'Referral scheme'),
            'schemeText' => Yii::t('app', 'Referral scheme'),
            'status' => Yii::t('app', 'Status'),
            'statusText' => Yii::t('app', 'Status'),
            'referralPaid' => Yii::t('app', 'Referral payments'),
            'paid' => Yii::t('app', 'Partner income'),
            'p_access' => Yii::t('app', 'Partner referral access'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(User::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUrl()
    {
        return $this->hasOne(ReferralUrl::className(), ['id' => 'url_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(UserReferralTransaction::className(), [
            'partner_id' => 'partner_id',
            'user_id' => 'user_id',
        ]);
    }

    /**
     * @inheritdoc
     * @return UserReferralQuery
     */
    public static function find()
    {
        return new UserReferralQuery(get_called_class());
    }

    public function applyPayment(Payment $payment, $autoSave = true)
    {
        if ($this->status >= static::STATUS_FINISHED) {
            return 0;
        }
        if (!($payment->user_id == $this->user_id && $payment->amount > 0)) {
            return 0;
        }
        $amount = 0;
        $newStatus = static::STATUS_ACTIVE;
        if (UserReferralTransaction::find()->where(['payment_id' => $payment->id])->exists()) {
            // по такому платежу уже было начисление
        } else if ($this->scheme == static::SCHEME_FIRST_ONLY) {
            // только первый платёж
            $previous = Payment::find()->where([
                'AND',
                ['status' => Payment::STATUS_COMPLETED],
                ['user_id' => $payment->user_id],
                ['<', 'at', $payment->at],
            ]);
            if (!$previous->exists()) {
                if ($fixed = Variable::sGet('s.settings.referralFixedFirst', $this->partner_id)) {
                    $amount += $fixed;
                }
                if ($percent = Variable::sGet('s.settings.referralPercentFirst', $this->partner_id)) {
                    $amount += $payment->amount * $percent / 100;
                }
                $newStatus = static::STATUS_FINISHED;
            }

        } else if ($this->scheme == static::SCHEME_LIFETIME_LIMITED) {
            $limit = ($l = Variable::sGet('s.settings.referralTimeLimit', $this->partner_id)) ? $l : 365;
            $expired = $limit > 0 && (time() > ($this->user->created_at + $limit * 86400));
            if ($expired) {
                $newStatus = static::STATUS_FINISHED;
            } else if ($percent = Variable::sGet('s.settings.referralPercent', $this->partner_id)) {
                $amount += $payment->amount * $percent / 100;
                $newStatus = static::STATUS_ACTIVE_PAID;
            }
        }
        if ($amount > 0) {
            $t = new Transaction([
                'user_id' => $this->partner_id,
                //'payment_id' => $payment->id,
                'amount' => $amount,
                'description' => Yii::t('app', 'Referral bonus for user: {username}', ['username' => $this->user->username]),
            ]);
            if ($t->save()) {
                $ut = new UserReferralTransaction([
                    'partner_id' => $this->partner_id,
                    'user_id' => $this->user_id,
                    'transaction_id' => $t->id,
                    'payment_id' => $payment->id,
                ]);
                if (!$ut->save()) {
                    $t->delete();
                }
            }
        }
        ReferralStats::addValues([
            'user_id' => $this->partner_id,
            'url_id' => $this->url_id,
            'datetime' => time(),
            'active' => $this->isActive ? 0 : 1,
            'paid' => $amount,
        ]);
        if ($this->status != $newStatus || $amount) {
            $this->paid += $amount;
            $this->status = $newStatus;
            if ($autoSave) {
                $this->save(false, ['status', 'paid']);
            }
        }
        return $amount;
    }

}
