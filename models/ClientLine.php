<?php

namespace app\models;

use app\base\tplModel;
use Yii;

/**
 * Класс ClientLine - модель линии(для связи).
 * Таблица в БД: {{%client_line}}
 *
 * @property integer $id Идентификатор линии
 * @property integer $user_id Идентификатор пользователя - владельца
 * @property integer $type_id Идентификатор типа
 * @property string $title Заголовок/название линии
 * @property string $info Информация для звонка (номер телефона для обычной связи)
 * @property string $description Описание линии
 *
 * @property User $user Пользователь - владелец
 * @property ClientRuleLine[] Связи с правилами для этой линии
 */
class ClientLine extends \yii\db\ActiveRecord
{

    use tplModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_line}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'info'], 'required'],
            [['user_id', 'type_id'], 'integer'],
            [['title'], 'string', 'min' => 3, 'max' => 70],
            [['info'], 'string', 'min' => 3, 'max' => 255],
            [['description'], 'string', 'max' => 255],
            [
                ['user_id', 'title'],
                'unique', 'targetAttribute' => ['user_id', 'title'],
                'message' => Yii::t('app', 'Phone line with such title already exists.'),
            ],
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
            'type_id' => Yii::t('app', 'Type ID'),
            'title' => Yii::t('app', 'Phone line title'),
            'info' => Yii::t('app', 'Phone line info'),
            'description' => Yii::t('app', 'Phone line description'),
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
    public function getClientRuleLines()
    {
        return $this->hasMany(ClientRuleLine::className(), ['line_id' => 'id']);
    }

}
