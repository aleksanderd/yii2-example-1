<?php

namespace app\models;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Transaction;
use yii\helpers\ArrayHelper;

/**
 * TransactionSearch represents the model behind the search form about `app\models\Transaction`.
 */
class TransactionSearch extends Transaction
{

    public $come;
    public $paymentLink;
    public $dateRange;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'payment_id', 'query_id', 'notification_id', 'at'], 'integer'],
            [['amount'], 'number'],
            [['description', 'details_data'], 'safe'],
            [['come', 'paymentLink', 'dateRange'], 'string'],
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
            'come' => Yii::t('app', 'Transaction direction'),
            'paymentLink' => Yii::t('app', 'Payment link'),
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
        $query = Transaction::find();

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

        if (isset($this->come)) {
            if ($this->come == 'in') {
                $query->andWhere(['>', 'amount', 0]);
            } else if ($this->come == 'out') {
                $query->andWhere(['<', 'amount', 0]);
            }
        }

        if (isset($this->paymentLink) && strlen($this->paymentLink) > 0) {
            $c = ($this->paymentLink == 'no') ? 'IS' : 'IS NOT';
            $query->andWhere([$c, 'payment_id', null]);
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
            'payment_id' => $this->payment_id,
            'query_id' => $this->query_id,
            'notification_id' => $this->notification_id,
            'at' => $this->at,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'details_data', $this->details_data]);

        //$sql = $query->createCommand()->rawSql;

        return $dataProvider;
    }
}
