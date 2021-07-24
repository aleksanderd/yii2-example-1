<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%promocode_activated}}".
 *
 * @property integer $at
 * @property integer $partner_id
 * @property integer $user_id
 * @property integer $promocode_id
 * @property integer $partner_transaction_id
 * @property integer $user_transaction_id
 *
 * @property User $partner
 * @property Transaction $partnerTransaction
 * @property Promocode $promocode
 * @property User $user
 * @property Transaction $userTransaction
 */
class PromocodeActivation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%promocode_activation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'user_id', 'promocode_id'], 'required'],
            [['at', 'partner_id', 'user_id', 'promocode_id', 'partner_transaction_id', 'user_transaction_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'at' => Yii::t('app', 'At'),
            'partner_id' => Yii::t('app', 'Partner ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'promocode_id' => Yii::t('app', 'Promocode ID'),
            'partner_transaction_id' => Yii::t('app', 'Partner Transaction ID'),
            'user_transaction_id' => Yii::t('app', 'User Transaction ID'),
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'at',
                'updatedAtAttribute' => false,
            ],
        ]);
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
    public function getPartnerTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'partner_transaction_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocode()
    {
        return $this->hasOne(Promocode::className(), ['id' => 'promocode_id']);
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
    public function getUserTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'user_transaction_id']);
    }

    /**
     * Активирует промокод: сохраняет запись активированных промокодов, создает транзакцию, если надо,
     * назначает реферала.
     *
     * @param bool $setReferral
     * @return bool
     * @throws \Exception
     */
    public function activate($setReferral = false)
    {
        if (!$this->save()) {
            return false;
        }
        $transaction = new Transaction([
            'user_id' => $this->user_id,
            'amount' => $this->promocode->amount,
            'description' => Yii::t('app', 'Promocode {code} income', ['code' => $this->promocode->code]),
        ]);
        if (!$transaction->save()) {
            $this->delete();
            return false;
        }
        $this->user_transaction_id = $transaction->id;
        $this->save(false, ['user_transaction_id']);

        if ($setReferral && !$transaction->user->partner) {
            $referral = new UserReferral([
                'partner_id' => $this->partner_id,
                'user_id' => $this->user_id,
                'scheme' => intval(Variable::sGet('u.settings.referralScheme', $this->partner_id)),
            ]);
            $referral->save();
        }
        return true;
    }
}
