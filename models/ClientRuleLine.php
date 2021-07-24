<?php

namespace app\models;

use Yii;

/**
 * Класс ClientRuleLine - модель связи правил [[ClientRule]] и линий [[ClientLine]].
 * Таблица в БД: {{%client_rule_line}}.
 *
 * @property integer $rule_id Идентификатор правила
 * @property integer $line_id Идентификатор линии
 * @property integer $priority Приоритет (для задания порядка линий в правиле)
 * @property string $options Дополнительный опции
 *
 * @property ClientLine $line Линия
 * @property ClientRule $rule Правило
 */
class ClientRuleLine extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_rule_line}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'line_id', 'priority'], 'integer'],
            //[['options_data'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', 'Rule ID'),
            'line_id' => Yii::t('app', 'Phone line ID'),
            'priority' => Yii::t('app', 'Priority'),
            'options' => Yii::t('app', 'Options'),
        ];
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
    public function getRule()
    {
        return $this->hasOne(ClientRule::className(), ['id' => 'rule_id']);
    }
}
