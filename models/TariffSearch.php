<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TariffSearch represents the model behind the search form about `app\models\Tariff`.
 */
class TariffSearch extends Tariff
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'renewable', 'lifetime', 'queries', 'minutes', 'messages', 'space'], 'integer'],
            [['title', 'desc', 'desc_details', 'desc_internal'], 'safe'],
            [['price'], 'number'],
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
        $query = Tariff::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'renewable' => $this->renewable,
            'price' => $this->price,
            'lifetime' => $this->lifetime,
            'queries' => $this->queries,
            'minutes' => $this->minutes,
            'messages' => $this->messages,
            'space' => $this->space,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'desc_details', $this->desc_details])
            ->andFilterWhere(['like', 'desc_internal', $this->desc_internal]);

        return $dataProvider;
    }
}
