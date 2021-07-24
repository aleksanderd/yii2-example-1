<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClientLineSearch represents the model behind the search form about `app\models\ClientLine`.
 */
class ClientLineSearch extends ClientLine
{

    /** @var string ID, title, url or description */
    public $itd;

    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'type_id'], 'integer'],
            [['itd', 'title', 'info', 'description'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'itd' => Yii::t('app', 'ID, title or description'),
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
        $query = ClientLine::find();

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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type_id' => $this->type_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['OR',
            ['id' => $this->itd],
            ['LIKE', 'title', $this->itd],
            ['LIKE', 'description', $this->itd],
        ]);
        return $dataProvider;
    }
}
