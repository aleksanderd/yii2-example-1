<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%client_query_call}}".
 *
 * @property integer $id
 * @property integer $query_id
 * @property integer $line_id
 * @property string $info
 * @property integer $started_at
 * @property integer $failed_at
 * @property integer $connected_at
 * @property integer $disconnected_at
 * @property integer $duration
 * @property float $cost
 * @property string $direction
 * @property float $client_price
 * @property float $client_cost
 *
 * @property float $currentPrice
 * @property ClientLine $line
 * @property ClientQuery $query
 */
class ClientQueryCall extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_query_call}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['query_id', 'line_id', 'started_at', 'failed_at', 'connected_at', 'disconnected_at', 'duration'], 'integer'],
            [['cost', 'client_price', 'client_cost'], 'number'],
            [['info', 'direction'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'query_id' => Yii::t('app', 'Query ID'),
            'line_id' => Yii::t('app', 'Phone line ID'),
            'call_info' => Yii::t('app', 'Call Info'),
            'started_at' => Yii::t('app', 'Started At'),
            'failed_at' => Yii::t('app', 'Failed At'),
            'connected_at' => Yii::t('app', 'Connected At'),
            'disconnected_at' => Yii::t('app', 'Disconnected At'),
            'duration' => Yii::t('app', 'Vi Duration'),
            'cost' => Yii::t('app', 'Vi Cost'),
            'direction' => Yii::t('app', 'Vi Direction'),
            'client_price' => Yii::t('app', 'Price'),
            'client_cost' => Yii::t('app', 'Cost'),
        ];
    }

    public function getUser()
    {
        return $this->query->user;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLine()
    {
        return $this->hasOne(ClientLine::className(), ['id' => 'line_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->hasOne(ClientQuery::className(), ['id' => 'query_id']);
    }

    /**
     * Обновляет цену и стоимость звонка.
     * @return $this
     */
    public function updateClientCost()
    {
        if (isset($this->query->user_tariff_id) && $this->query->user_tariff_id > 0) {
            $this->client_cost = 0;
            $this->client_price = 0;
        } else {
            $this->client_price = Variable::sGet('s.price.callMinute',
                $this->query->user_id, $this->query->site_id, $this->query->page_id);
            $this->client_cost = $this->duration * $this->client_price / 60;
        }
        return $this;
    }

}
