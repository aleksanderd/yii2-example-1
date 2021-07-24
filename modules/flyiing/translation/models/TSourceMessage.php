<?php

namespace flyiing\translation\models;

use Yii;

/**
 * This is the model class for table "{{%t_source_message}}".
 *
 * @property integer $id
 * @property string $category
 * @property string $message
 *
 * @property TMessage[] $messages
 */
class TSourceMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%t_source_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['category'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category' => Yii::t('app', 'Category'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(TMessage::className(), ['id' => 'id']);
    }

    /**
     * Возвращает объект по заданной категории и исходному сообщению, если такого еще нет - создаёт.
     *
     * @param string $category Категория сообщений
     * @param string $message Исходное сообщение
     * @return TSourceMessage|null
     */
    public static function findOrCreate($category, $message)
    {
        $key = compact('category', 'message');
        if (!($model = TSourceMessage::findOne($key))) {
            $model = new TSourceMessage($key);
            $model->save();
        }
        return $model;
    }

}
