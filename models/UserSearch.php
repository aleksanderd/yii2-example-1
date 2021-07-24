<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class UserSearch extends \dektrium\user\models\UserSearch
{

    /** @var string id, username или email для поиска */
    public $ine;

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['ine', 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ine' => Yii::t('app', 'ID, Username or e-mail'),
        ]);
    }

    public function search($params)
    {
        $query = User::find();
        $query->joinWith('transactions');
        $query->groupBy('{{%user}}.`id`');

        $query->select([
            '{{%user}}.*',
            'balance' => 'IFNULL(SUM({{%transaction}}.`amount`), 0)',
//            'balance' => 'SUM({{%transaction}}.`amount`)',
//            'balance' => Transaction::find()->where(['user_id' => '{{%user}}.id'])->sum('amount'),
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $sortAttributes = array_merge($dataProvider->sort->attributes, [
            'balance',
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['username' => SORT_DESC],
            'attributes' => $sortAttributes,
        ]);
        if ($this->load($params)) {
            if (!$this->validate()) {
                return $dataProvider;
            }
        }

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', 'created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['registration_ip' => $this->registration_ip]);

        if (isset($this->ine) && strlen($this->ine)) {
            $query->andWhere([
                'OR',
                ['{{%user}}.`id`' => $this->ine],
                ['LIKE', '{{%user}}.`username`', $this->ine],
                ['LIKE', '{{%user}}.`email`', $this->ine],
            ]);
        }

        return $dataProvider;
    }
}

