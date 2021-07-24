<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_referral_transaction}}".
 *
 * @property integer $partner_id
 * @property integer $user_id
 * @property integer $transaction_id
 * @property integer $payment_id
 * @property integer $at
 *
 * @property Payment $payment
 * @property User $partner
 * @property Transaction $transaction
 * @property User $user
 * @property UserReferral $referral
 */
class UserReferralTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_referral_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'user_id', 'transaction_id', 'payment_id'], 'required'],
            [['partner_id', 'user_id', 'transaction_id', 'payment_id', 'at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'partner_id' => Yii::t('app', 'Partner ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'transaction_id' => Yii::t('app', 'Transaction'),
            'transaction.amount' => Yii::t('app', 'Partner income'),
            'payment_id' => Yii::t('app', 'Referral payment'),
            'payment.amount' => Yii::t('app', 'Referral payment'),
            'at' => Yii::t('app', 'At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
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
    public function getTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'transaction_id']);
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
    public function getReferral()
    {
        return $this->hasOne(User::className(), [
            'partner_id' => 'partner_id',
            'user_id' => 'user_id',
        ]);
    }
}
