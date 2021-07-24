<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\base\tplModel;

/**
 * This is the model class for table "{{%s_message}}".
 *
 * @property integer $id
 * @property integer $ticket_id
 * @property integer $parent_id
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $message
 *
 * @property User $user
 * @property SMessage $parent
 * @property SMessage[] $sMessages
 * @property STicket $ticket
 */
class SMessage extends \yii\db\ActiveRecord
{
    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
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
        return '{{%s_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ticket_id', 'user_id', 'message'], 'required'],
            [['ticket_id', 'parent_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['message'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ticket_id' => Yii::t('app', 'Ticket ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'message' => Yii::t('app', 'Message'),
        ];
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
    public function getParent()
    {
        return $this->hasOne(SMessage::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSMessages()
    {
        return $this->hasMany(SMessage::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(STicket::className(), ['id' => 'ticket_id']);
    }

    /**
     * @inheritdoc
     * @return \app\models\query\SMessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\SMessageQuery(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            Notification::onSMessage($this);
        }
        parent::afterSave($insert, $changedAttributes);
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
        if ($ticket = $this->ticket) {
            $result = array_merge($result, $ticket->tplPlaceholders($prefix . 'ticket.'));
        }
        $tz = Variable::sGet('u.settings.timezone', $this->user_id);
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        $result = array_merge($result, static::tplDatetimePlaceholders($this->created_at, $tz, $prefix . 'createdDatetime'));
        $result = array_merge($result, static::tplDatetimePlaceholders($this->updated_at, $tz, $prefix . 'updatedDatetime'));
        $result['{url}'] = Variable::sGet('s.settings.baseUrl', $this->user_id) . 's-ticket/view?id=' . $ticket->id . '#s-message-' . $this->id;
        return $result;
    }
}
