<?php

namespace app\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * PaymentSearch represents the model behind the search form about `app\models\Payment`.
 * @property-read bool $isDatetimeGrouped
 * @property-read bool $isGrouped
 */
class PaymentSearch extends Payment
{

    const GROUP_BY_ALL = 'all';
    const GROUP_BY_USER = 'user_id';
    const GROUP_BY_DT_HOUR = 'dt_hour';
    const GROUP_BY_DT_DAY = 'dt_day';
    const GROUP_BY_DT_MONTH = 'dt_month';
    const GROUP_BY_DT_YEAR = 'dt_year';

    public $dateRange;
    public $groupBy;

    public static function groupByLabels()
    {
        return [
            static::GROUP_BY_ALL => Yii::t('app', 'Group all'),
            static::GROUP_BY_USER => Yii::t('app', 'Group by user'),
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'at', 'method', 'status'], 'integer'],
            [['amount'], 'number'],
            [['description', 'details_data'], 'safe'],
            [['dateRange', 'groupBy'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'dateRange' => Yii::t('app', 'Date range'),
        ]);
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
        $query = Payment::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['at' => 'desc'],
            ],
        ]);

        if ($this->load($params)) {
            if (!$this->validate()) {
                return $dataProvider;
            }
        }

        if ($this->isGrouped) {

            $selectColumns = [
                'id' => 'id',
                'user_id' => 'user_id',
                'at' => 'at',
                'method' => 'method',
                'status' => 'status',
                'amount' => 'SUM(`amount`)',
                'description' => 'description',
                'details_data' => 'details_data',
                'promocode_id' => 'promocode_id',
            ];

            if ($this->groupBy == static::GROUP_BY_DT_YEAR) {
                $selectColumns['at1'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-01-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_MONTH) {
                $selectColumns['at1'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-01 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_DAY) {
                $selectColumns['at1'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-%d 00:00:00"))';
            } else if ($this->groupBy == static::GROUP_BY_DT_HOUR) {
                $selectColumns['at1'] = 'UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(`at`), "%Y-%m-%d %H:00:00"))';
            }

            if ($this->groupBy == static::GROUP_BY_ALL) {
                $dataProvider->totalCount = 1;
            } else {
                if ($this->isDatetimeGrouped) {
                    $query->groupBy('at1');
                } else {
                    $query->groupBy($this->groupBy);
                }
            }
            $query->select($selectColumns);
        }

        if (isset($this->dateRange) && count(($dates = explode(' - ', $this->dateRange))) == 2) {
            $ds = DateTime::createFromFormat('!d.m.yy', $dates[0])->getTimestamp();
            $de = DateTime::createFromFormat('!d.m.yy', $dates[1])->getTimestamp() + 86400;
            $query->andWhere(['AND',
                ['>', 'at', $ds],
                ['<', 'at', $de],
            ]);
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'at' => $this->at,
            'amount' => $this->amount,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        //$sql = $query->createCommand()->rawSql;

        return $dataProvider;
    }
}
