<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClientRuleSearch represents the model behind the search form about `app\models\ClientRule`.
 */
class NotificationSearch extends Notification
{
    public $to_from;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_id', 'page_id', 'query_id', 'type', 'at', 'status'], 'integer'],
            [['body'], 'string'],
            [['to_from', 'from', 'to', 'subject', 'description'], 'string', 'max' => 255]
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
        return array_merge(parent::attributeLabels(), [
            'to_from' => Yii::t('app', 'to/from search'),
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
        $query = Notification::find();

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

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'page_id' => $this->page_id,
        ]);

        $query->andFilterWhere(['OR',
            ['LIKE', 'to', $this->to_from],
            ['LIKE', 'from', $this->to_from],
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
