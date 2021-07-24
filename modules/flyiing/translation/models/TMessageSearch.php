<?php

namespace flyiing\translation\models;

use Yii;
use yii\data\ActiveDataProvider;

class TMessageSearch extends TMessage
{

    public $text;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['translation', 'text'], 'string'],
            [['language'], 'string', 'max' => 16]
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'text' => Yii::t('app', 'Text'),
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
        $query = TMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'language', $this->language])
            ->andFilterWhere(['like', 'translation', $this->translation]);

        if (isset($this->text) && strlen($this->text)) {
            $sources = TSourceMessage::find()
                ->where(['like', 'message', $this->text])
                ->select('id');
            $query->andWhere(['or', ['like', 'translation', $this->text], ['in', 'id', $sources]]);
        }

        return $dataProvider;
    }

}
