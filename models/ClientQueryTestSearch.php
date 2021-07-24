<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ClientQueryTest;

/**
 * ClientQueryTestSearch represents the model behind the search form about `app\models\ClientQueryTest`.
 */
class ClientQueryTestSearch extends ClientQueryTest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'site_id', 'at'], 'integer'],
            [['call_info', 'data', 'title', 'description', 'options'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ClientQueryTest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'at' => $this->at,
        ]);

        $query->andFilterWhere(['like', 'call_info', $this->call_info])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'options', $this->options]);

        return $dataProvider;
    }
}
