<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%client_visit}}".
 *
 * @property integer $id
 * @property integer $previous_id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $at
 * @property string $ip
 * @property string $ref_url
 * @property string $user_agent
 *
 * @property ClientSite $site
 * @property ClientVisit $previous
 * @property ClientVisit[] $clientVisits
 * @property User $user
 * @property WidgetHit[] $widgetHits
 */
class ClientVisit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_visit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['previous_id', 'user_id', 'site_id', 'at'], 'integer'],
            [['user_id', 'at'], 'required'],
            [['ip', 'ref_url', 'user_agent'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'previous_id' => Yii::t('app', 'Previous ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'site_id' => Yii::t('app', 'Site ID'),
            'at' => Yii::t('app', 'At'),
            'ip' => Yii::t('app', 'IP'),
            'ref_url' => Yii::t('app', 'Referrer Url'),
            'user_agent' => Yii::t('app', 'User Agent'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(ClientSite::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrevious()
    {
        return $this->hasOne(ClientVisit::className(), ['id' => 'previous_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNext()
    {
        return $this->hasOne(ClientVisit::className(), ['previous_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWidgetHits()
    {
        return $this->hasMany(WidgetHit::className(), ['visit_id' => 'id']);
    }
}
