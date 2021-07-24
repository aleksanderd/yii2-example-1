<?php

namespace app\models;

use app\helpers\DataHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель сгруппированной статистики. Таблица БД "{{%conversion}}".
 * Данные группируются по пользователю(`user_id`), сайту(`site_id`) и времени(`datetime` и `period`).
 * В зависимости от значние поля `period` запись содержит статистику за минуту, час, день или месяц, начиная с даты и
 * времени, установленном в `datetime`.
 *
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $datetime
 * @property integer $period
 * @property integer $hits
 * @property integer $visits
 * @property integer $visits_unique
 * @property integer $queries
 * @property integer $queries_unique
 * @property integer $queries_unpaid
 * @property integer $queries_success
 * @property integer $queries_calls
 * @property integer $record_time
 * @property string $client_cost
 * @property string $cost
 * @property integer $manual_wins
 * @property integer $manual_queries
 * @property integer $tr_total_ignored
 * @property integer $tr_total_wins
 * @property integer $tr_total_queries
 * @property integer $tr_scrollEnd_ignored
 * @property integer $tr_scrollEnd_wins
 * @property integer $tr_scrollEnd_queries
 * @property integer $tr_selectText_ignored
 * @property integer $tr_selectText_wins
 * @property integer $tr_selectText_queries
 * @property integer $tr_mouseExit_ignored
 * @property integer $tr_mouseExit_wins
 * @property integer $tr_mouseExit_queries
 * @property integer $tr_period_ignored
 * @property integer $tr_period_wins
 * @property integer $tr_period_queries
 *
 * @property-read integer $datetime_period
 * @property-read integer $queries_failed
 * @property-read integer $visits_return
 * @property-read float $conversion
 * @property-read float $conversion_success
 * @property-read integer $wins
 * @property-read float $wins_per_visit
 * @property-read float $mwins_per_visit
 * @property-read float $twins_per_visit
 * @property-read float $queries_per_wins
 * @property-read float $success_per_query
 * @property ClientSite $site
 * @property User $user
 */
class Conversion extends \yii\db\ActiveRecord
{

    public function getDatetime_period()
    {
        return $this->datetime;
    }

    public function getQueries_failed()
    {
        return $this->queries - $this->queries_success;
    }

    public function getConversion()
    {
        return $this->visits_unique > 0 ? 100 * $this->queries_unique / $this->visits_unique : 0;
    }

    public function getConversion_success()
    {
        return $this->visits_unique > 0 ? 100 * $this->queries_success / $this->visits_unique : 0;
    }

    public function getVisits_return()
    {
        return $this->visits - $this->visits_unique;
    }

    public function getWins()
    {
        return $this->manual_wins + $this->tr_total_wins;
    }

    public function getWins_per_visit()
    {
        if ($this->visits_unique > 0) {
            return 100 * ($this->manual_wins + $this->tr_total_wins) / $this->visits_unique;
        } else {
            return 0;
        }
    }

    public function getMwins_per_visit()
    {
        if ($this->visits_unique > 0) {
            return 100 * $this->manual_wins / $this->visits_unique;
        } else {
            return 0;
        }
    }

    public function getTwins_per_visit()
    {
        if ($this->visits_unique > 0) {
            return 100 * $this->tr_total_wins / $this->visits_unique;
        } else {
            return 0;
        }
    }

    public function getQueries_per_wins()
    {
        if ($this->wins > 0) {
            return 100 * $this->queries / $this->wins;
        } else {
            return 0;
        }
    }

    public function getSuccess_per_query()
    {
        if ($this->queries > 0) {
            return 100 * $this->queries_success / $this->queries;
        } else {
            return 0;
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conversion}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_id', 'datetime', 'period'], 'required'],
            [[
                'user_id', 'site_id', 'datetime', 'period',
                'hits', 'visits', 'visits_unique',
                'queries', 'queries_unique', 'queries_unpaid', 'queries_success', 'queries_calls', 'record_time',
                'manual_wins', 'manual_queries',
                'tr_total_ignored', 'tr_total_wins', 'tr_total_queries',
                'tr_scrollEnd_ignored', 'tr_scrollEnd_wins', 'tr_scrollEnd_queries',
                'tr_selectText_ignored', 'tr_selectText_wins', 'tr_selectText_queries',
                'tr_mouseExit_ignored', 'tr_mouseExit_wins', 'tr_mouseExit_queries',
                'tr_period_ignored', 'tr_period_wins', 'tr_period_queries',
            ], 'integer'],

            [['client_cost', 'cost'], 'number'],
            [['datetime_period', 'queries_failed'], 'integer'],
            [['conversion'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'datetime' => Yii::t('app', 'Datetime'),
            'datetime_period' => Yii::t('app', 'Datetime'),
            'period' => Yii::t('app', 'Period'),
            'hits' => Yii::t('app', 'Hits'),
            'visits' => Yii::t('app', 'Total visits'),
            'visits_unique' => Yii::t('app', 'Unique visits'),
            'queries' => Yii::t('app', 'Total queries'),
            'queries_unique' => Yii::t('app', 'Unique queries'),
            'queries_unpaid' => Yii::t('app', 'Unpaid queries'),
            'queries_success' => Yii::t('app', 'Success queries'),
            'queries_failed' => Yii::t('app', 'Failed queries'),
            'queries_calls' => Yii::t('app', 'Queries calls'),
            'record_time' => Yii::t('app', 'Record time'),
            'client_cost' => Yii::t('app', 'Client cost'),
            'cost' => Yii::t('app', 'Cost'),
            'manual_wins' => Yii::t('app', 'Manual Wins'),
            'manual_queries' => Yii::t('app', 'Manual Queries'),
            'tr_total_ignored' => Yii::t('app', 'Tr Total Ignored'),
            'tr_total_wins' => Yii::t('app', 'Tr Total Wins'),
            'tr_total_queries' => Yii::t('app', 'Tr Total Queries'),
            'tr_scrollEnd_ignored' => Yii::t('app', 'Tr Scroll End Ignored'),
            'tr_scrollEnd_wins' => Yii::t('app', 'Tr Scroll End Wins'),
            'tr_scrollEnd_queries' => Yii::t('app', 'Tr Scroll End Queries'),
            'tr_selectText_ignored' => Yii::t('app', 'Tr Select Text Ignored'),
            'tr_selectText_wins' => Yii::t('app', 'Tr Select Text Wins'),
            'tr_selectText_queries' => Yii::t('app', 'Tr Select Text Queries'),
            'tr_mouseExit_ignored' => Yii::t('app', 'Tr Mouse Exit Ignored'),
            'tr_mouseExit_wins' => Yii::t('app', 'Tr Mouse Exit Wins'),
            'tr_mouseExit_queries' => Yii::t('app', 'Tr Mouse Exit Queries'),
            'tr_period_ignored' => Yii::t('app', 'Tr Period Ignored'),
            'tr_period_wins' => Yii::t('app', 'Tr Period Wins'),
            'tr_period_queries' => Yii::t('app', 'Tr Period Queries'),
            'conversion' => Yii::t('app', 'Conversion'),
            'conversion_success' => Yii::t('app', 'Conversion (success)'),
            'wins' => Yii::t('app', 'Total wins'),
            'wins_per_visit' => Yii::t('app', 'Wins / visit'),
            'mwins_per_visit' => Yii::t('app', 'M.Wins / visit'),
            'twins_per_visit' => Yii::t('app', 'T.Wins / visit'),
        ];
    }

    public function aggregateAttributes()
    {
        return array_diff($this->attributes(), [
            'user_id', 'site_id', 'datetime', 'period',
        ]);
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public static function addValues($config)
    {
        $key = [];
        if (!($key['user_id'] = ArrayHelper::remove($config, 'user_id'))) {
            return false;
        }
        if (!($key['site_id'] = ArrayHelper::remove($config, 'site_id'))) {
            return false;
        }
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

//        if (!($model = Conversion::findOne($key))) {
//            $model = new Conversion($key);
//        }
//        $updated = [];
//        foreach ($config as $name => $value) {
//            if ($model->hasAttribute($name)) {
//                $updated[] = $name;
//                $model->{$name} += $value;
//            }
//        }
//        return $model->save(false);

        $db = Yii::$app->db;
        $sql = sprintf('INSERT INTO {{%%conversion}} SET `user_id`=%d, `site_id`=%d, `period`=%d, `datetime`=%d',
            $key['user_id'], $key['site_id'], $key['period'], $key['datetime']);
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
