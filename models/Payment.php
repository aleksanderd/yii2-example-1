<?php

namespace app\models;

use app\base\tplModel;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $admin_id
 * @property integer $at
 * @property integer $method
 * @property integer $status
 * @property string $amount
 * @property string $description
 * @property string $details_data
 *
 * @property Promocode $promocode
 * @property User $user
 * @property User $admin
 * @property Transaction[] $transactions
 * @property UserReferralTransaction $referralTransaction
 */
class Payment extends \yii\db\ActiveRecord
{
    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    const METHOD_BASIC = 0;
    const METHOD_PAYPAL = 1;
    const METHOD_YAKASSA = 2;
    const METHOD_WALLETONE = 3;
    const METHOD_PROMO = 4;
    const METHOD_PAYMASTER = 5;

    const STATUS_ERROR = -100;
    const STATUS_NEW = 0;
    const STATUS_CANCELED  = 1;
    const STATUS_COMPLETED = 100;

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
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $statusChanged = $insert || isset($changedAttributes['status']);
        if ($statusChanged && $this->status == static::STATUS_COMPLETED && $this->amount > 0) {
            if ($referral = UserReferral::find()->where(['user_id' => $this->user_id])->one()) {
                /** @var \app\models\UserReferral $referral */
                $referral->applyPayment($this);

                $url = $referral->url;
                // если есть реф.ссылка и она с подарком
                if (isset($url) && $url->gift_amount > 0) {
                    $code = $referral->url->code;
                    $giftStateVar = 'u.state.giftCode.' . $code . '.used';

                    // если подарок еще не получен
                    if (!Variable::sGet($giftStateVar, $referral->user_id)) {
                        $min = Variable::sGet('s.settings.referralGiftPaymentsRequired', $referral->partner_id);
                        $sum = Payment::find()->where([
                            'user_id' => $this->user_id,
                            'status' => Payment::STATUS_COMPLETED,
                        ])->sum('amount');

                        // минималка по платежам для получения подарка
                        if ($sum > $min) {
                            $gift = new Transaction([
                                'user_id' => $referral->user_id,
                                'amount' => $url->gift_amount,
                                'description' => Yii::t('app', 'Gift for using promo code {code}', compact('code')),
                            ]);

                            // сохраням подарок и статсы
                            if ($gift->save()) {
                                Variable::sSet($giftStateVar, time(), $referral->user_id);
                                ReferralStats::addValues([
                                    'user_id' => $url->user_id,
                                    'url_id' => $url->id,
                                    'datetime' => time(),
                                    'gifts_activated' => 1,
                                    'gifts_paid' => $gift->amount,
                                ]);
                            }
                        }
                    }
                }
            }
            Notification::onPayment($this);
        }
    }

    public static function methodLabels()
    {
        return [
            static::METHOD_PAYPAL => Yii::t('app', 'Paypal'),
            static::METHOD_PAYMASTER => Yii::t('app', 'Paymaster'),
            static::METHOD_YAKASSA => Yii::t('app', 'Yandex Kassa'),
            static::METHOD_WALLETONE => Yii::t('app', 'Walletone'),
            static::METHOD_PROMO => Yii::t('app', 'Promocode'),
        ];
    }

    public static function statusLabels()
    {
        return [
            static::STATUS_ERROR => Yii::t('app', 'Payment error'),
            static::STATUS_NEW => Yii::t('app', 'New payment'),
            static::STATUS_CANCELED => Yii::t('app', 'Canceled payment'),
            static::STATUS_COMPLETED => Yii::t('app', 'Completed payment'),
        ];
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
        return '{{%payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'method'], 'required'],
            [['user_id', 'admin_id', 'at', 'method', 'status', 'promocode_id'], 'integer'],
            [['amount'], 'number', 'min' => 300],
            [['details_data'], 'string'],
            [['description'], 'string', 'max' => 255],
            [['promocode_id'], 'exist', 'targetClass' => Promocode::className(), 'targetAttribute' => 'id'],
            ['promocode_id', 'required', 'when' => function ($model) {
                return $model->method === static::METHOD_PROMO;
            }],
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
            'at' => Yii::t('app', 'At'),
            'method' => Yii::t('app', 'Method'),
            'status' => Yii::t('app', 'Status'),
            'amount' => Yii::t('app', 'Amount'),
            'description' => Yii::t('app', 'Description'),
            'details_data' => Yii::t('app', 'Details Data'),
            'promocode_id' => Yii::t('app', 'Promocode ID'),
        ];
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
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['payment_id' => 'id']);
    }

    /**
     * Get promocode if used.
     * @return \yii\db\ActiveQuery
     */
    public function getPromocode()
    {
        return $this->hasOne(Promocode::className(), ['id' => 'promocode_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferralTransaction()
    {
        return $this->hasOne(UserReferralTransaction::className(), ['payment_id' => 'id']);
    }

    public function createTransaction()
    {
        if (!($transaction = Transaction::findOne(['payment_id' => $this->id]))) {
            $transaction = new Transaction(['payment_id' => $this->id]);
        }
        $transaction->user_id = $this->user_id;
        $transaction->amount = $this->amount;
        $transaction->description = $this->description;
        return $transaction;
    }

    /**
     * Возвращает массив строк для замены в шаблонах.
     *
     * Поля непосредственно из таблицы БД:
     * * {id}
     * * {user_id}
     * * {method} - Целочисленное
     * * {status} - Целочисленное
     * * {amount} - Сумма платежа
     * * {descriptions} - Описание платежа
     *
     * Другие поля:
     * * {timezone} - Временная зона из настроек пользователя.
     * * {datetime} - Строковое выражение текущего времени в выбранной временной зоне.
     * * {datetime.utc} - Строковое выражение текущего времени в зоне UTC.
     * * {callInfo} - инфа для связи с клиентом, но скрытая звёздочками, если баланс юзера <0.
     *
     * Кроме того, можно использовать поля из связанных моделей, обращаясь к ним через используя префиксы:
     * * [[User]]
     *   * {user.username}
     *   * {user.email}
     *   * {user.balance}
     *   * ...и тд. см. [[User::tplPlaceholders()]].
     *
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);
        $tz = Yii::$app->timeZone;
        if ($user = $this->user) {
            if ($t = Variable::sGet('u.settings.timezone', $this->user_id, $this->id)) {
                $tz = $t;
            }
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        return $result;
    }
}
