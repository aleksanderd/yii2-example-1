<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Класс Transaction - модель фин.транзакции.
 * Таблица в БД: {{%transaction}}.
 *
 * @property integer $id Идентификатор транзакции
 * @property integer $user_id Идентификатор пользователя
 * @property integer $admin_id
 * @property integer $payment_id Идентификатор платежа
 * @property integer $query_id Идентификатор запроса
 * @property integer $notification_id Идентификатор уведомления
 * @property integer $user_tariff_id
 * @property integer $at Время транзакции
 * @property string $amount Сумма
 * @property string $description Описание
 * @property string $details_data Доп.данные
 *
 * @property Notification $notification
 * @property Payment $payment
 * @property ClientQuery $query
 * @property User $user
 * @property User $admin
 * @property UserTariff $userTariff
 * @property UserReferralTransaction $referralTransaction
 */
class Transaction extends \yii\db\ActiveRecord
{

    public $details;

    public function afterFind()
    {
        $this->details = unserialize($this->details_data);
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (is_array($this->details)) {
            $this->details_data = serialize($this->details);
        }
        $this->amount = round($this->amount, 2);
        return parent::beforeSave($insert);

    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->user->balance <= $this->user->minBalance) {
            Notification::onUser($this->user, 'minBalance');
        }
    }

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
        return '{{%transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount'], 'required'],
            [['user_id', 'admin_id', 'payment_id', 'query_id', 'notification_id', 'at'], 'integer'],
            [['amount'], 'number'],
            [['details_data'], 'string'],
            [['description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'payment_id' => Yii::t('app', 'Payment ID'),
            'query_id' => Yii::t('app', 'Query ID'),
            'notification_id' => Yii::t('app', 'Notification ID'),
            'at' => Yii::t('app', 'At'),
            'amount' => Yii::t('app', 'Amount'),
            'description' => Yii::t('app', 'Description'),
            'details_data' => Yii::t('app', 'Details Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotification()
    {
        return $this->hasOne(Notification::className(), ['id' => 'notification_id']);
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
    public function getQuery()
    {
        return $this->hasOne(ClientQuery::className(), ['id' => 'query_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTariff()
    {
        return $this->hasOne(UserTariff::className(), ['id' => 'user_tariff_id']);
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
    public function getAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserReferralTransaction()
    {
        return $this->hasOne(UserReferralTransaction::className(), ['transaction_id' => 'id']);
    }

}
