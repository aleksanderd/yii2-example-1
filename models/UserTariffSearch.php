<?php

namespace app\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * UserTariffSearch represents the model behind the search form about `app\models\UserTariff`.
 */
class UserTariffSearch extends UserTariff
{
    public $dateRange;
    public $dateRangeSubj;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'user_id', 'tariff_id', 'status', 'renew', 'started_at', 'finished_at', 'lifetime',
                'queries', 'queries_used', 'seconds', 'seconds_used', 'messages', 'messages_used', 'space', 'space_used'
            ], 'integer'],
            [['dateRange', 'dateRangeSubj'], 'string'],
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
            'dateRangeSubj' => Yii::t('app', 'Date range subject'),
        ]);
    }

    public static function getLtCol()
    {
        $ltMonth = 'UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(`started_at`), INTERVAL `lifetime` MONTH))';
        return 'IF(`lifetime_measure` > 1, ' . $ltMonth . ', `started_at` + `lifetime` * 86400)';
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
        $query = UserTariff::find();


//        $ltMonth = 'UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(`started_at`), INTERVAL `lifetime` MONTH))';
        $ltCol = static::getLtCol();
        $query->select([
            '*',
            'lifetimeEnd' => 'IF(`lifetime` > 0, ' . $ltCol . ', NULL)',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $ltSort = 'IFNULL(`lifetimeEnd`, 777777777777)';
        $sortAttributes = array_merge($dataProvider->sort->attributes, [
            'lifetimeEnd',
            'lifetimeEnd' => [
                'desc' => [$ltSort => SORT_DESC],
                'asc' => [$ltSort => SORT_ASC],
            ],
        ]);
        $dataProvider->setSort([
            'attributes' => $sortAttributes,
        ]);

        if ($this->load($params)) {
            if (!$this->validate()) {
                return $dataProvider;
            }
        }

        if (isset($this->dateRange) && count(($dates = explode(' - ', $this->dateRange))) == 2) {

            $ds = DateTime::createFromFormat('!d.m.yy', $dates[0])->getTimestamp();
            $de = DateTime::createFromFormat('!d.m.yy', $dates[1])->getTimestamp() + 86400;

            $subj = explode(',', ArrayHelper::getValue($this, 'dateRangeSubj', 'started_at,lifetimeEnd,finished_at'));
            $cond = ['OR'];
            foreach ($subj as $s) {
                if ($s == 'lifetimeEnd') {
                    $s = $ltCol;
                }
                $cond[] = ['AND',
                    ['>', $s, $ds],
                    ['<', $s, $de],
                ];
            }
            $query->andWhere($cond);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tariff_id' => $this->tariff_id,
            'status' => $this->status,
            'renew' => $this->renew,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'lifetime' => $this->lifetime,
            'queries' => $this->queries,
            'queries_used' => $this->queries_used,
            'seconds' => $this->seconds,
            'seconds_used' => $this->seconds_used,
            'messages' => $this->messages,
            'messages_used' => $this->messages_used,
            'space' => $this->space,
            'space_used' => $this->space_used,
        ]);
        //$sql = $query->createCommand()->rawSql;
        return $dataProvider;
    }
}
