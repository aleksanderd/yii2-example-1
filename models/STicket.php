<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use app\base\tplModel;

/**
 * This is the model class for table "{{%s_ticket}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $topic_id
 * @property integer $status
 * @property string $title
 *
 * @property SMessage[] $sMessages
 * @property ClientSite $site
 * @property User $user
 */
class STicket extends \yii\db\ActiveRecord
{
    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    const TOPIC_GENERAL = 0;
    const TOPIC_FINANCE = 10;
    const TOPIC_TECH = 20;

    const STATUS_NEW = 0;
    const STATUS_OPEN = 10;
    const STATUS_REPLIED = 100;
    const STATUS_CLOSED = 1000;

    public $message;

    public static function topicLabels()
    {
        return [
            static::TOPIC_GENERAL => Yii::t('app', 'General topic'),
            static::TOPIC_FINANCE => Yii::t('app', 'Finance topic'),
            static::TOPIC_TECH => Yii::t('app', 'Technical topic'),
        ];
    }

    public static function statusLabels()
    {
        return [
            static::STATUS_NEW => Yii::t('app', 'New topic'),
            static::STATUS_OPEN => Yii::t('app', 'Open topic'),
            static::STATUS_REPLIED => Yii::t('app', 'Replied topic'),
            static::STATUS_CLOSED => Yii::t('app', 'Closed topic'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%s_ticket}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title'], 'required'],
            [['user_id', 'site_id', 'created_at', 'updated_at', 'topic_id', 'status'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['message'], 'string'],
            [['message'], 'required', 'on' => 'create'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
            'topic_id' => Yii::t('app', 'Support topic'),
            'status' => Yii::t('app', 'Status'),
            'title' => Yii::t('app', 'Title'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMessages()
    {
        return $this->hasMany(SMessage::className(), ['ticket_id' => 'id']);
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return \app\models\query\STicketQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\STicketQuery(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert && isset($this->message)) {
            $msg = $this->createMessage(['message' => $this->message]);
            $msg->save();
        }
    }

    /**
     * @param array $config
     * @return SMessage
     */
    public function createMessage($config = [])
    {
        return new SMessage(ArrayHelper::merge([
            'user_id' => $this->user_id,
            'ticket_id' => $this->id,
        ], $config));
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);
        if ($user = $this->user) {
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }
        if ($site = $this->site) {
            $result = array_merge($result, $site->tplPlaceholders($prefix . 'site.'));
        }
        $tz = Variable::sGet('u.settings.timezone', $this->user_id);
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        $result = array_merge($result, static::tplDatetimePlaceholders($this->created_at, $tz, $prefix . 'createdDatetime'));
        $result = array_merge($result, static::tplDatetimePlaceholders($this->updated_at, $tz, $prefix . 'updatedDatetime'));
        return $result;
    }

}
