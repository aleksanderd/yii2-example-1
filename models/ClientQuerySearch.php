<?php

namespace app\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ClientQuerySearch represents the model behind the search form about `app\models\ClientQuery`.
 *
 * @property-read bool $isDatetimeGrouped
 * @property-read bool $isGrouped
 */
class ClientQuerySearch extends ClientQuery
{
    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    const GROUP_BY_ALL = 'all';
    const GROUP_BY_USER = 'user_id';
    const GROUP_BY_USER_SITE = 'user_id, site_id';
    const GROUP_BY_DT_HOUR = 'dt_hour';
    const GROUP_BY_DT_DAY = 'dt_day';
    const GROUP_BY_DT_MONTH = 'dt_month';
    const GROUP_BY_DT_YEAR = 'dt_year';

    public $groupBy;
    public $dateRange;

    public static function groupByLabels()
    {
        return [
            static::GROUP_BY_ALL => Yii::t('app', 'Group all'),
            static::GROUP_BY_USER => Yii::t('app', 'Group by user'),
            static::GROUP_BY_USER_SITE => Yii::t('app', 'Group by website'),
            static::GROUP_BY_DT_HOUR => Yii::t('app', 'Group by hour'),
            static::GROUP_BY_DT_DAY => Yii::t('app', 'Group by day'),
            static::GROUP_BY_DT_MONTH => Yii::t('app', 'Group by month'),
            static::GROUP_BY_DT_YEAR => Yii::t('app', 'Group by year'),
        ];
    }

    public function getIsGrouped()
    {
        return isset(static::groupByLabels()[$this->groupBy]);
    }

    public function getIsDatetimeGrouped()
    {
        return in_array($this->groupBy, [
            static::GROUP_BY_DT_HOUR,
            static::GROUP_BY_DT_DAY,
            static::GROUP_BY_DT_MONTH,
            static::GROUP_BY_DT_YEAR,
        ]);
    }

    public static function statusFilterOptions()
    {
        return [
            'success' => Yii::t('app', 'Success queries'),
            'failed' => Yii::t('app', 'Failed queries'),
            static::STATUS_UNPAID => Yii::t('app', 'Unpaid queries'),
        ];
    }

    public static function tariffFilterOptions()
    {
        return [
            'tariff' => Yii::t('app', 'Queries with tariff'),
            'no-tariff' => Yii::t('app', 'Queries without tariff'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'test_id', 'user_id', 'site_id', 'rule_id', 'at', 'time', 'record_time'], 'integer'],
            [['call_info', 'record_data', 'result_data', 'data', 'user_tariff_id'], 'safe'],
            [['status', 'groupBy', 'dateRange'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'groupBy' => Yii::t('app', 'Grouping'),
            'dateRange' => Yii::t('app', 'Date range'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ClientQuery::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['at' => 'desc'],
            ],
        ]);

        $sortAttributes = array_merge($dataProvider->sort->attributes, [
            'datetime_period',
        ]);
        foreach ($sortAttributes as $k => $v) {
            if (is_array($v)) {
                $attr = $k;
            } else {
                $attr = $v;
                unset($sortAttributes[$k]);
                $sortAttributes[$attr] = [];
            }
            $sortAttributes[$attr]['default'] = SORT_DESC;
        }

        if ($this->load($params)) {
            if (!$this->validate()) {
                return $dataProvider;
            }
        }

        $defaultOrder = !$this->isGrouped || $this->isDatetimeGrouped ?
            ['datetime_period' => SORT_DESC] : ['client_cost' => SORT_DESC];
        $dataProvider->setSort([
            'attributes' => $sortAttributes,
            'defaultOrder' => $defaultOrder,
        ]);

        $cols = [];
        foreach ($this->attributes() as $a) {
            $cols[$a] = $a;
        }

        if ($this->isGrouped) {

            if ($this->groupBy == static::GROUP_BY_DT_YEAR) {
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-01-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_MONTH) {
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_DAY) {
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-%d 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_HOUR) { // static::GROUP_BY_DT_HOUR
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-%d %H:00:00"))';
            } else {
                $cols['datetime_period'] = 'at';
            }

            $cols['time'] = 'SUM(`time`)';
            $cols['record_time'] = 'SUM(`record_time`)';
            $cols['client_cost'] = 'SUM(`client_cost`)';
            $cols['cost'] = 'SUM(`cost`)';

            //$cols = array_merge($cols, []);
            if ($this->groupBy == static::GROUP_BY_ALL) {
                $dataProvider->totalCount = 1;
            } else {
                if ($this->isDatetimeGrouped) {
                    $query->groupBy('datetime_period');
                } else {
                    $query->groupBy($this->groupBy);
                }
            }
        } else {
            $cols = array_merge($cols, [
                'datetime_period' => 'at',
            ]);
        }
        $query->select($cols);

        if (isset($this->dateRange) && count(($dates = explode(' - ', $this->dateRange))) == 2) {
            $ds = DateTime::createFromFormat('!d.m.yy', $dates[0])->getTimestamp();
            $de = DateTime::createFromFormat('!d.m.yy', $dates[1])->getTimestamp() + 86400;
            $query->andWhere(['AND',
                ['>=', 'at', $ds],
                ['<', 'at', $de],
            ]);
        }

        $query->andFilterWhere([
            'user_id' => $this->subjectUsersIds,
        ]);
        //
        $query->andWhere(['IS NOT', 'user_id', null]);

        if (isset($this->status) && strlen($this->status) > 0) {
            if ($this->status == 'success') {
                $query->andWhere(['>=', 'status', static::STATUS_COMM_SUCCESS]);
            } else if ($this->status == 'failed') {
                $query->andWhere(['<', 'status', static::STATUS_COMM_SUCCESS]);
            } else {
                $query->andWhere(['status' => $this->status]);
            }
        }
        if (isset($this->user_tariff_id) && strlen($this->user_tariff_id) > 0) {
            if ($this->user_tariff_id == 'tariff') {
                $query->andWhere(['IS NOT', 'user_tariff_id', null]);
            } else if ($this->user_tariff_id == 'no-tariff') {
                $query->andWhere(['IS', 'user_tariff_id', null]);
            }
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'test_id' => $this->test_id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'rule_id' => $this->rule_id,
            'time' => $this->time,
            'record_time' => $this->record_time,
        ]);

        $query->andFilterWhere(['like', 'call_info', $this->call_info])
            ->andFilterWhere(['like', 'record_data', $this->record_data])
            ->andFilterWhere(['like', 'result_data', $this->result_data])
            ->andFilterWhere(['like', 'data', $this->data]);

        //$sql = $query->createCommand()->rawSql;

        return $dataProvider;
    }
}
