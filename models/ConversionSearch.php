<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 *
 * @property-read bool $isDatetimeGrouped
 * @property-read string $hash
 * @property-read bool $isGrouped
 */
class ConversionSearch extends Conversion {

    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    const GROUP_BY_ALL = 'all';
    const GROUP_BY_USER = 'user_id';
    const GROUP_BY_USER_SITE = 'user_id, site_id';
    const GROUP_BY_DT_HOUR = 'dt_hour';
    const GROUP_BY_DT_DAY = 'dt_day';
    const GROUP_BY_DT_MONTH = 'dt_month';
    const GROUP_BY_DT_YEAR = 'dt_year';

    public $dtStart;
    public $dtEnd;
    public $groupBy;

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

    public function getHash()
    {
        $props = array_merge($this->getAttributes(), [
            'dtStart' => null, 'dtEnd' => null, 'groupBy' => null,
        ]);
        $src = '';
        foreach ($props as $p => $v) {
            $src .= ArrayHelper::getValue($this, $p, '') . ':';
        }
        return md5($src);
    }

    public function rules()
    {
        return [
            [['dtStart', 'dtEnd'], 'integer'],
            [['groupBy'], 'string'],
            [['user_id', 'site_id', 'datetime', 'period', 'hits', 'visits', 'visits_unique', 'queries', 'queries_unique', 'queries_unpaid', 'queries_success', 'queries_calls', 'record_time'], 'integer'],
            [['client_cost', 'cost'], 'number']
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'dtStart' => Yii::t('app', 'Start date'),
            'dtEnd' => Yii::t('app', 'End date'),
            'groupBy' => Yii::t('app', 'Grouping'),
        ]);
    }


    public function search($params)
    {
        $query = Conversion::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [1, 500],
            ],
        ]);

        $sortAttributes = array_merge($dataProvider->sort->attributes, [
            'datetime_period',
            'queries_failed',
            'conversion',
            'conversion_success',
            'wins',
            'wins_per_visit' => [
                'asc' => ['wins_per_visit' => SORT_ASC, 'visits_unique' => SORT_DESC],
                'desc' => ['wins_per_visit' => SORT_DESC, 'visits_unique' => SORT_DESC],
                'default' => 'asc',
            ],
            'mwins_per_visit' => [
                'asc' => ['mwins_per_visit' => SORT_ASC, 'visits_unique' => SORT_DESC],
                'desc' => ['mwins_per_visit' => SORT_DESC, 'visits_unique' => SORT_DESC],
                'default' => 'asc',
            ],
            'twins_per_visit' => [
                'asc' => ['twins_per_visit' => SORT_ASC, 'visits_unique' => SORT_DESC],
                'desc' => ['twins_per_visit' => SORT_DESC, 'visits_unique' => SORT_DESC],
                'default' => 'asc',
            ],
            'queries_per_wins' => [
                'asc' => ['queries_per_wins' => SORT_ASC, 'wins' => SORT_DESC],
                'desc' => ['queries_per_wins' => SORT_DESC, 'wins' => SORT_DESC],
                'default' => 'asc',
            ],
            'success_per_query' => [
                'asc' => ['success_per_query' => SORT_ASC, 'queries' => SORT_DESC],
                'desc' => ['success_per_query' => SORT_DESC, 'queries' => SORT_DESC],
                'default' => 'asc',
            ],
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

        $query->andFilterWhere([
            'user_id' => $this->subjectUsersIds,
        ]);

        $defaultOrder = !$this->isGrouped || $this->isDatetimeGrouped ?
            ['datetime_period' => SORT_DESC] : ['queries_unique' => SORT_DESC];
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
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-01-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_MONTH) {
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_DAY) {
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d 00:00:00"))';
            } else { // static::GROUP_BY_DT_HOUR
                $cols['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d %H:00:00"))';
            }
            foreach ($this->aggregateAttributes() as $a) {
                $cols[$a] = sprintf('SUM(`%s`)', $a);
            }

            $cols = array_merge($cols, [
                'queries_failed' => '(SUM(`queries`) - SUM(`queries_success`))',
                'conversion' => '100 * SUM(`queries`) / SUM(`visits_unique`)',
                'conversion_success' => '100 * SUM(`queries_success`) / SUM(`visits_unique`)',
                'wins' => 'SUM(`manual_wins`) + SUM(`tr_total_wins`)',
                'wins_per_visit' => '100 * SUM(`manual_wins` + `tr_total_wins`) / SUM(`visits_unique`)',
                'mwins_per_visit' => '100 * SUM(`manual_wins`) / SUM(`visits_unique`)',
                'twins_per_visit' => '100 * SUM(`tr_total_wins`) / SUM(`visits_unique`)',
                'queries_per_wins' => '100 * SUM(`queries`) / (SUM(`manual_wins`) + SUM(`tr_total_wins`))',
                'success_per_query' => '100 * SUM(`queries_success`) / SUM(`queries`)',
            ]);
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
                'datetime_period' => 'datetime',
                'queries_failed' => '(`queries` - `queries_success`)',
                'conversion' => '100 * `queries` / `visits_unique`',
                'conversion_success' => '100 * `queries_success` / `visits_unique`',
                'wins' => '`manual_wins` + `tr_total_wins`',
                'wins_per_visit' => '100 * (`manual_wins` + `tr_total_wins`) / `visits_unique`',
                'mwins_per_visit' => '100 * `manual_wins` / `visits_unique`',
                'twins_per_visit' => '100 * `tr_total_wins` / `visits_unique`',
                'queries_per_wins' => '100 * `queries` / (`manual_wins` + `tr_total_wins`)',
                'success_per_query' => '100 * `queries_success` / `queries`',
            ]);
        }
        $query->select($cols);

        $query->andFilterWhere(['>=', 'datetime', $this->dtStart]);
        $query->andFilterWhere(['<=', 'datetime', $this->dtEnd]);
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'period' => $this->period,
            'datetime' => $this->datetime,
        ]);

        return $dataProvider;
    }

    /**
     * @param array $params
     * @param bool $previous
     * @return \app\models\Conversion[]
     */
    public function getModels($params = [], $previous = false)
    {
        $clone = clone $this;
        if ($previous) {
            $period = $clone->dtEnd - $clone->dtStart;
            $clone->dtEnd = $clone->dtStart;
            $clone->dtStart = $clone->dtStart - $period;
        }
        /** @var \yii\db\Query $query */
        $query = $clone->search($params)->query;
        return $query->all();
    }

}
