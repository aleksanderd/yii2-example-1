<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%black_call_info}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $call_info
 * @property string $comment
 *
 * @property User $user
 */
class BlackCallInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%black_call_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['call_info'], 'required'],
            [['comment'], 'string'],
            [['call_info'], 'string', 'max' => 255],
            [['user_id', 'call_info'], 'unique', 'targetAttribute' => ['user_id', 'call_info'], 'message' => 'The combination of User ID and Call Info has already been taken.'],
        ];
    }

    public function beforeValidate()
    {
        if (intval($this->user_id) < 1) {
            $this->user_id = null;
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'call_info' => Yii::t('app', 'Call Info'),
            'comment' => Yii::t('app', 'Comment'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
