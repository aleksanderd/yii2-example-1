<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VariableValueSearch represents the model behind the search form about `app\models\VariableValue`.
 */
class VariableValueSearch extends VariableValue
{
    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variable_id', 'user_id', 'site_id', 'page_id'], 'integer'],
            [['value_data'], 'safe'],
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
        $query = VariableValue::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'variable_id' => $this->variable_id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'page_id' => $this->page_id,
        ]);

        $query->andFilterWhere(['like', 'value_data', $this->value_data]);

        return $dataProvider;
    }
}
