<?php

namespace app\models;

use app\base\tplModel;
use app\models\query\PayoutQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%payout}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $transaction_id
 * @property integer $status
 * @property string $amount
 * @property string $comment
 * @property string $details_data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string statusText
 * @property string title
 * @property boolean isWritable
 * @property Transaction $transaction
 * @property User $user
 */
class Payout extends \yii\db\ActiveRecord
{
    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    const STATUS_REQUEST = 1;

    const STATUS_IN_PROCESS = 10;
    const STATUS_REJECTED = 11;
    const STATUS_COMPLETE = 100;

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['status']) || $insert) {
            if ($this->status == static::STATUS_REQUEST) {
                Notification::onPayout($this, 'payoutRequest');
            } else if ($this->status == static::STATUS_COMPLETE) {
                Notification::onPayout($this, 'payoutComplete');
            } else if ($this->status == static::STATUS_REJECTED) {
                Notification::onPayout($this, 'payoutRejected');
            }
        }
    }

    /**
     * @return array
     */
    public static function statusLabels()
    {
        return [
            static::STATUS_REJECTED => Yii::t('app', 'Payout rejected'),
            static::STATUS_REQUEST => Yii::t('app', 'Payout request'),
            static::STATUS_IN_PROCESS => Yii::t('app', 'Payout in process'),
            static::STATUS_COMPLETE => Yii::t('app', 'Payout complete'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return ArrayHelper::getValue(static::statusLabels(), $this->status,
            Yii::t('app', 'Unknown payment status'));
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return '#' . $this->id;
    }

    /**
     * @return bool
     */
    public function getIsWritable()
    {
        return ($this->status > 0) && ($this->status < static::STATUS_IN_PROCESS);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payout}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'transaction_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['comment', 'details_data'], 'string'],
            [['transaction_id'], 'unique'],
            ['amount', 'validateAmount'],
        ];
    }

    public function validateAmount($attribute)
    {
        $fmt = Yii::$app->formatter;
        $min = Variable::sGet('s.settings.payoutMin', $this->user_id);
        if ($this->amount < $min) {
            $this->addError($attribute, Yii::t('app', 'Minimal payout amount') .': '. $fmt->asCurrency($min));
            return false;
        }
        $userMax = $this->user->partnerMaxPayout;
        $payoutMax = Variable::sGet('s.settings.payoutMax', $this->user_id);
        // берём минимальное
        $max = $userMax < $payoutMax ? $userMax : $payoutMax;
        if ($this->amount > $max) {
            $this->addError($attribute, Yii::t('app', 'Maximal payout amount') .': '. $fmt->asCurrency($max));
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'transaction_id' => Yii::t('app', 'Transaction'),
            'status' => Yii::t('app', 'Status'),
            'statusText' => Yii::t('app', 'Status'),
            'amount' => Yii::t('app', 'Amount'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
        ];
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
     * @inheritdoc
     * @return PayoutQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PayoutQuery(get_called_class());
    }

    /**
     * Возвращает массив строк для замены в шаблонах уведомлений про выплаты.
     *
     * Поля непосредственно из таблицы БД:
     * * {id}
     * * {status} - Целочисленное
     * * {statusText} - Статус текстом
     * * {amount} - Сумма выплаты
     * * {comment} - Комментарий
     * * {created_at} - Целочисленное, timestamp
     * * {updated_at} - Целочисленное, timestamp
     * * {createdAt} - Время создания текстом
     * * {updatedAt} - Вермя обновления текстом
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
        $fmt = Yii::$app->formatter;
        $result = $this->base_tplPlaceholders($prefix);

        if ($user = $this->user) {
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }

        $result['{'.$prefix.'statusText}'] = $this->statusText;
        $result['{'.$prefix.'createdAt}'] = $fmt->asDatetime($this->created_at);
        $result['{'.$prefix.'updatedAt}'] = $fmt->asDatetime($this->created_at);

        $tz = Variable::sGet('u.settings.timezone', $this->user_id);
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));

        return $result;
    }

}
