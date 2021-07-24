<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClientSiteSearch represents the model behind the search form about `app\models\ClientSite`.
 */
class ClientSiteSearch extends ClientSite
{

    /** @var string ID, title, url or description */
    public $itud;

    /** @var integer|array|\yii\db\Query */
    public $subjectUsersIds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['itud', 'title', 'description', 'url', 'w_check_result'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'itud' => Yii::t('app', 'ID, title, url or description'),
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
        $query = ClientSite::find();

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
            'w_check_result' => $this->w_check_result,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'url', $this->url]);

        $query->andFilterWhere(['OR',
            ['id' => $this->itud],
            ['LIKE', 'title', $this->itud],
            ['LIKE', 'url', $this->itud],
            ['LIKE', 'description', $this->itud],
        ]);
        return $dataProvider;
    }
}
