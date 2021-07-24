<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\query\ReferralUrlQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%referral_url}}".
 *
 * @property integer $id
 * @property integer $status
 * @property integer $user_id
 * @property string $title
 * @property integer $promocode_id
 * @property string $code
 * @property float $gift_amount
 *
 * @property boolean $isDefault
 * @property string $titleText
 * @property string $url
 * @property string $statusText
 * @property ReferralStats[] $referralStats
 * @property Promocode $promocode
 * @property User $user
 * @property UserReferral[] $userReferrals
 */
class ReferralUrl extends \yii\db\ActiveRecord
{

    const STATUS_DELETED = -1;
    const STATUS_ENABLED = 100;

    const DEFAULT_TITLE = '*';

    const ID_DELIMITER = '-';

    /** @var ReferralStats|null */
    protected $_stats;

    public function beforeSave($insert)
    {
        if (!isset($this->code)) {
            $this->code = $this->generateCode();
        }
        return parent::beforeSave($insert);
    }

    public function init()
    {
        if (!isset($this->gift_amount)) {
            $this->gift_amount = 0;
        }
        parent::init();
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public static function statusLabels()
    {
        return [
            static::STATUS_ENABLED => Yii::t('app', 'Enabled'),
            static::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%referral_url}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $maxGiftAmount = Variable::sGet('s.settings.referralGiftMax', $this->user_id);
        return [
            [['status', 'user_id', 'promocode_id', 'created_at'], 'integer'],
            [['user_id', 'title'], 'required'],
            [['gift_amount'], 'number', 'max' => $maxGiftAmount],
            [['code'], 'string', 'max' => 11],
            [['code'], 'unique'],
            [['title'], 'string', 'max' => 255],
            [
                ['status', 'user_id', 'title'],
                'unique',
                'targetAttribute' => ['status', 'user_id', 'title'],
                'message' => Yii::t('app', 'Such title already used.'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'Referral url'),
            'status' => Yii::t('app', 'Status'),
            'statusText' => Yii::t('app', 'Status'),
            'user_id' => Yii::t('app', 'User ID'),
            'user.username' => Yii::t('app', 'Partner'),
            'title' => Yii::t('app', 'Title'),
            'titleText' => Yii::t('app', 'Title'),
            'promocode_id' => Yii::t('app', 'Promocode ID'),
            'promocode.code' => Yii::t('app', 'Promocode'),
            'created_at' => Yii::t('app', 'Created at'),
            'code' => Yii::t('app', 'Referral code'),
            'gift_amount' => Yii::t('app', 'Gift amount'),
            'stats.visits' => Yii::t('app', 'Referral visits'),
            'stats.registered' => Yii::t('app', 'Registrations'),
            'stats.active' => Yii::t('app', 'Active users'),
            'stats.paid' => Yii::t('app', 'Earned'),
            'stats.gifts_activated' => Yii::t('app', 'Gifts activated'),
            'stats.gifts_paid' => Yii::t('app', 'Gifts paid'),
        ];
    }

    /**
     * Возвращает модель реф.урла по умолчанию для заданного юзера. Создаёт запись если такой ещё нет.
     *
     * @param integer $user_id
     * @return ReferralUrl|bool|null|static
     */
    public static function defaultReferralUrl($user_id)
    {
        $config = [
            'user_id' => $user_id,
            'title' => static::DEFAULT_TITLE,
            'status' => static::STATUS_ENABLED,
        ];
        $model = ReferralUrl::findOne($config);
        if (!$model) {
            $model = new ReferralUrl($config);
            if (!$model->save()) {
                return false;
            }
        }
        return $model;
    }

    public function generateCode($length = 5)
    {
        $chars = 'QWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        $charsCount = strlen($chars);
        $result = false;
        while (!$result) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $ci = rand(0, $charsCount - 1);
                $code .= $chars[$ci];
            }
            if ($this->validate(['code'])) {
                $result = $code;
            }
        }
        return $result;
    }

    /**
     * Возвращает ReferralStats модель со значениями сумм по урлу.
     *
     * @return null|ReferralStats
     */
    public function getStats()
    {
        if (!isset($this->_stats)) {
            $this->_stats = $this->getReferralStats()->select([
                'visits' => 'SUM(`visits`)',
                'registered' => 'SUM(`registered`)',
                'active' => 'SUM(`active`)',
                'paid' => 'SUM(`paid`)',
                'gifts_activated' => 'SUM(`gifts_activated`)',
                'gifts_paid' => 'SUM(`gifts_paid`)',
            ])->one();
        }
        return $this->_stats;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->title === static::DEFAULT_TITLE;
    }

    /**
     * @return string
     */
    public function getTitleText()
    {
        $text = $this->isDefault ? ' : ' . Yii::t('app', 'Default referral url') : '';
        return $this->title . $text;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $baseUrl = Variable::sGet('s.settings.url');
        $tpl = $this->isDefault ? '%s?r=%d' : '%s?r=%d%s%d';
        return sprintf($tpl, $baseUrl, $this->user_id, static::ID_DELIMITER, $this->id);
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return ArrayHelper::getValue(static::statusLabels(), $this->status, Yii::t('app', 'Unknown status'));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferralStats()
    {
        return $this->hasMany(ReferralStats::className(), ['url_id' => 'id']);
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
    public function getUserReferrals()
    {
        return $this->hasMany(UserReferral::className(), ['url_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return ReferralUrlQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReferralUrlQuery(get_called_class());
    }
}
