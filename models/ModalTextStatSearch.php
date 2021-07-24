<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ModalTextStatSearch represents the model behind the search form about `app\models\ModalTextStat`.
 * @property-read bool $isDatetimeGrouped
 * @property-read bool $isGrouped
 */
class ModalTextStatSearch extends ModalTextStat
{

    const GROUP_BY_ALL = 'all';
    const GROUP_BY_TEXT = 'text_id';
    const GROUP_BY_USER = 'user_id, text_id';
    const GROUP_BY_USER_SITE = 'user_id, site_id, text_id';
    const GROUP_BY_DT_HOUR = 'dt_hour';
    const GROUP_BY_DT_DAY = 'dt_day';
    const GROUP_BY_DT_MONTH = 'dt_month';
    const GROUP_BY_DT_YEAR = 'dt_year';
    const GROUP_BY_TRIGGER = 'trigger, text_id';

    public $groupBy;
    public $dateRange;

    public static function groupByLabels()
    {
        return [
//            static::GROUP_BY_ALL => Yii::t('app', 'Group all'),
            static::GROUP_BY_TEXT => Yii::t('app', 'Group by text'),
            static::GROUP_BY_USER => Yii::t('app', 'Group by user'),
            static::GROUP_BY_USER_SITE => Yii::t('app', 'Group by website'),
//            static::GROUP_BY_DT_HOUR => Yii::t('app', 'Group by hour'),
            static::GROUP_BY_DT_DAY => Yii::t('app', 'Group by day'),
            static::GROUP_BY_DT_MONTH => Yii::t('app', 'Group by month'),
            static::GROUP_BY_DT_YEAR => Yii::t('app', 'Group by year'),
            static::GROUP_BY_TRIGGER => Yii::t('app', 'Group by trigger'),
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text_id', 'user_id', 'site_id', 'datetime', 'period', 'wins', 'wins_uni', 'queries', 'queries_uni'], 'integer'],
            [['trigger', 'dateRange', 'groupBy'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
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
        $query = ModalTextStat::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $sortAttributes = array_merge($dataProvider->sort->attributes, [
            'datetime_period',
            'ctr',
            'ctr_uni',
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

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $defaultOrder = !$this->isGrouped || $this->isDatetimeGrouped ?
            ['datetime_period' => SORT_DESC] : ['ctr_uni' => SORT_DESC];
        $dataProvider->setSort([
            'attributes' => $sortAttributes,
            'defaultOrder' => $defaultOrder,
        ]);

        $selectColumns = [
            'text_id' => 'text_id',
            'user_id' => 'user_id',
            'site_id' => 'site_id',
            'period' => 'period',
            'datetime' => 'datetime',
            'datetime_period' => 'datetime',
            'trigger' => 'trigger',
            'wins' => 'wins',
            'wins_uni' => 'wins_uni',
            'queries' => 'queries',
            'queries_uni' => 'queries_uni',
            'ctr' => '100 * `queries`/`wins`',
            'ctr_uni' => '100 * `queries_uni`/`wins_uni`',
        ];

        if ($this->isGrouped) {

            if ($this->groupBy == static::GROUP_BY_DT_YEAR) {
                $selectColumns['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-01-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_MONTH) {
                $selectColumns['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_DAY) {
                $selectColumns['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d 00:00:00"))';
            } else {
                $selectColumns['datetime_period'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d %H:00:00"))';
            }
            $selectColumns['datetime'] = $selectColumns['datetime_period'];
            $selectColumns['wins'] = 'SUM(`wins`)';
            $selectColumns['wins_uni'] = 'SUM(`wins_uni`)';
            $selectColumns['queries'] = 'SUM(`queries`)';
            $selectColumns['queries_uni'] = 'SUM(`queries_uni`)';
            $selectColumns['ctr'] = '100 * SUM(`queries`) / SUM(`wins`)';
            $selectColumns['ctr_uni'] = '100 * SUM(`queries_uni`) / SUM(`wins_uni`)';

            if ($this->groupBy == static::GROUP_BY_ALL) {
                $dataProvider->totalCount = 1;
            } else {
                if ($this->isDatetimeGrouped) {
                    $query->groupBy('datetime_period, text_id');
                } else {
                    $query->groupBy($this->groupBy);
                }
            }
        }
        $query->select($selectColumns);

        $query->andFilterWhere([
            'text_id' => $this->text_id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'datetime' => $this->datetime,
            'period' => $this->period,
            'wins' => $this->wins,
            'wins_uni' => $this->wins_uni,
            'queries' => $this->queries,
        ]);

        if (isset($this->trigger) && strlen($this->trigger) > 0) {
            if ($this->trigger == 'tr_only') {
                $query->andWhere(['>', 'trigger', 0]);
            } else {
                $query->andWhere(['trigger' => $this->trigger]);
            }
        }

        $sql = $query->createCommand()->rawSql;

        return $dataProvider;
    }
}
