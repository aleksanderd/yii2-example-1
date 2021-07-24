<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%widget_hit}}".
 *
 * @property integer $id
 * @property integer $visit_id
 * @property integer $page_id
 * @property integer $at
 * @property string $ip
 * @property string $url
 *
 * @property ClientPage $page
 * @property ClientVisit $visit
 * @property ClientQuery $queries
 */
class WidgetHit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%widget_hit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['visit_id', 'at'], 'required'],
            [['visit_id', 'page_id', 'at'], 'integer'],
            [['ip', 'url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'visit_id' => Yii::t('app', 'Visit ID'),
            'page_id' => Yii::t('app', 'Page ID'),
            'at' => Yii::t('app', 'At'),
            'ip' => Yii::t('app', 'Ip'),
            'url' => Yii::t('app', 'Url'),
        ];
    }

    /**
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(ClientPage::className(), ['id' => 'page_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(ClientVisit::className(), ['id' => 'visit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQueries()
    {
        return $this->hasMany(ClientQuery::className(), ['hit_id' => 'id']);
    }

}
