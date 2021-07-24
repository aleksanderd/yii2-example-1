<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClientQueryCallSearch represents the model behind the search form about `app\models\ClientQueryCall`.
 */
class ClientQueryCallSearch extends ClientQueryCall
{

    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'query_id', 'line_id', 'started_at', 'failed_at', 'connected_at', 'disconnected_at', 'duration'], 'integer'],
            [['cost'], 'number'],
            [['info', 'direction'], 'string', 'max' => 255],
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
        $query = ClientQueryCall::find();
        $query->joinWith('query');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['started_at' => 'desc'],
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
            'query_id' => $this->query_id,
            'line_id' => $this->line_id,
        ]);

        $query->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(['like', 'data', $this->direction]);

        return $dataProvider;
    }
}
