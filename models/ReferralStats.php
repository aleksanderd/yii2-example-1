<?php

namespace app\models;

use app\helpers\DataHelper;
use Yii;
use app\models\query\ReferralStatsQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%referral_stats}}".
 *
 * @property integer $user_id
 * @property integer $datetime
 * @property integer $period
 * @property integer $url_id
 * @property integer $visits
 * @property integer $visits_unique
 * @property integer $registered
 * @property integer $active
 * @property float $paid
 * @property integer $gifts_activated
 * @property float $gifts_paid
 *
 * @property ReferralUrl $url
 * @property User $user
 */
class ReferralStats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%referral_stats}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'datetime', 'url_id'], 'required'],
            [[
                'user_id',
                'datetime',
                'period',
                'url_id',
                'visits',
                'visits_unique',
                'registered',
                'active',
                'gifts_activated',
            ], 'integer'],

            [['paid', 'gifts_paid'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'datetime' => Yii::t('app', 'Datetime'),
            'url_id' => Yii::t('app', 'Url ID'),
            'visits' => Yii::t('app', 'Visits'),
            'visits_unique' => Yii::t('app', 'Unique visits'),
            'registered' => Yii::t('app', 'Registered'),
            'active' => Yii::t('app', 'Active'),
            'paid' => Yii::t('app', 'Earned'),
            'gifts_active' => Yii::t('app', 'Gifts activated'),
            'gifts_paid' => Yii::t('app', 'Gifts paid'),
        ];
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
     * @inheritdoc
     * @return ReferralStatsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReferralStatsQuery(get_called_class());
    }

    public static function addValues($config)
    {
        $key = [];
        if (!($key['user_id'] = ArrayHelper::remove($config, 'user_id'))) {
            return false;
        }
        if (!($key['datetime'] = ArrayHelper::remove($config, 'datetime'))) {
            return false;
        }
        if (!($key['url_id'] = ArrayHelper::remove($config, 'url_id'))) {
            return false;
        }
        if (count($config) < 1) {
            return false;
        }
        $key['period'] = DataHelper::PERIOD_DAY;
        $key['datetime'] = DataHelper::truncateDatetime($key['datetime'], $key['period']);
        $db = Yii::$app->db;
        $sql = sprintf('INSERT INTO {{%%referral_stats}} SET `user_id`=%d, `url_id`=%d, `period`=%d, `datetime`=%d',
            $key['user_id'], $key['url_id'], $key['period'], $key['datetime']);
        $updateSql = '';
        foreach ($config as $name => $value) {
            $sql .= sprintf(', `%s`=%f', $name, $value);
            $updateSql .= sprintf('`%s`=`%s`+%f, ', $name, $name, $value);
        }
        $updateSql = substr($updateSql, 0, -2);
        $sql .= ' ON DUPLICATE KEY UPDATE ' . $updateSql;
        return $db->createCommand($sql)->execute();
    }

}
