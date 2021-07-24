<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClientRuleSearch represents the model behind the search form about `app\models\ClientRule`.
 */
class ClientRuleSearch extends ClientRule
{
    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'site_id', 'priority'], 'integer'],
            [['title', 'description', 'condition_data', 'result_data'], 'safe'],
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
        $query = ClientRule::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['priority' => SORT_DESC],
            ],
        ]);

        if ($this->load($params)) {
            if (!$this->validate()) {
                return $dataProvider;
            }
        }

        $query->andFilterWhere([
            'user_id' => $this->subjectUsersIds,
        ]);

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'page_id' => $this->page_id,
            'priority' => $this->priority,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'condition_data', $this->condition_data])
            ->andFilterWhere(['like', 'result_data', $this->result_data]);

        return $dataProvider;
    }
}
