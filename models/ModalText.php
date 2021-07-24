<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%modal_text}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $language
 * @property string $title
 * @property string $m_title
 * @property string $m_submit
 * @property string $m_description
 *
 * @property User $user
 */
class ModalText extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%modal_text}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['title', 'm_description'], 'required'],
            [['m_description'], 'string'],
            [['language', 'title', 'm_title', 'm_submit'], 'string', 'max' => 255],
            [['user_id', 'title'], 'unique', 'targetAttribute' => ['user_id', 'title'], 'message' => 'The combination of User ID and Title has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'language' => Yii::t('app', 'Language'),
            'title' => Yii::t('app', 'Title'),
            'm_title' => Yii::t('app', 'M Title'),
            'm_submit' => Yii::t('app', 'M Submit'),
            'm_description' => Yii::t('app', 'M Description'),
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
     * @inheritdoc
     * @return \app\models\query\ModalTextQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ModalTextQuery(get_called_class());
    }
}
