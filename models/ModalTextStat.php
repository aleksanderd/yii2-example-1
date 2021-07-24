<?php

namespace app\models;

use app\helpers\DataHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%modal_text_stat}}".
 *
 * @property integer $text_id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $datetime
 * @property integer $period
 * @property integer $trigger
 * @property integer $wins
 * @property integer $wins_uni
 * @property integer $queries
 * @property integer $queries_uni
 *
 * @property integer $datetime_period
 * @property float $ctr
 * @property float $ctr_uni
 * @property ClientSite $site
 * @property ModalText $text
 * @property User $user
 */
class ModalTextStat extends \yii\db\ActiveRecord
{

    public function getCtr()
    {
        return $this->wins > 0 ? 100.0 * $this->queries / $this->wins : 0;
    }

    public function getCtr_uni()
    {
        return $this->wins_uni > 0 ? 100.0 * $this->queries_uni / $this->wins_uni : 0;
    }

    public function getDatetime_period()
    {
        return $this->datetime;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%modal_text_stat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text_id', 'user_id', 'site_id', 'datetime', 'period', 'trigger'], 'required'],
            [['text_id', 'user_id', 'site_id', 'datetime', 'period', 'trigger', 'wins', 'wins_uni', 'queries', 'queries_uni'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'text_id' => Yii::t('app', 'Modal text'),
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'datetime' => Yii::t('app', 'Datetime'),
            'datetime_period' => Yii::t('app', 'Datetime period'),
            'period' => Yii::t('app', 'Period'),
            'trigger' => Yii::t('app', 'Trigger'),
            'wins' => Yii::t('app', 'Wins'),
            'wins_uni' => Yii::t('app', 'Wins Uni'),
            'queries' => Yii::t('app', 'Queries'),
            'queries_uni' => Yii::t('app', 'Queries Uni'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(ClientSite::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getText()
    {
        return $this->hasOne(ModalText::className(), ['id' => 'text_id']);
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
     * @return \app\models\query\ModalTextStatQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ModalTextStatQuery(get_called_class());
    }

    public static function addValues($config)
    {
        $key = [];
        if (!($key['text_id'] = ArrayHelper::remove($config, 'text_id'))) {
            return false;
        }
        if (!($key['user_id'] = ArrayHelper::remove($config, 'user_id'))) {
            return false;
        }
        if (!($key['site_id'] = ArrayHelper::remove($config, 'site_id'))) {
            return false;
        }
        $key['trigger'] = ArrayHelper::remove($config, 'trigger', 0);
        if (!($key['datetime'] = ArrayHelper::remove($config, 'datetime'))) {
            return false;
        }
        if (!DataHelper::isPeriodValid($key['period'] = ArrayHelper::remove($config, 'period', DataHelper::PERIOD_HOUR))) {
            $key['period'] = DataHelper::PERIOD_HOUR;
        }
        if (count($config) < 1) {
            return false;
        }
        $key['datetime'] = DataHelper::truncateDatetime($key['datetime'], $key['period']);

        $db = Yii::$app->db;
        $sql = sprintf('INSERT INTO {{%%modal_text_stat}} SET `text_id`=%d, `user_id`=%d, `site_id`=%d, `trigger`=%d, `period`=%d, `datetime`=%d',
            $key['text_id'], $key['user_id'], $key['site_id'], $key['trigger'], $key['period'], $key['datetime']);
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
