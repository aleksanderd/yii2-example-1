<?php

namespace app\models;

use app\models\query\VariableValueQuery;
use Yii;

/**
 * This is the model class for table "{{%variable_value}}".
 *
 * @property integer $variable_id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $page_id
 * @property resource $value_data
 * @property mixed $value
 *
 * @property ClientPage $page
 * @property ClientSite $site
 * @property User $user
 * @property Variable $variable
 */
class VariableValue extends \yii\db\ActiveRecord
{

    /**
     * @return VariableValueQuery
     */
    public static function find()
    {
        return new VariableValueQuery(get_called_class());
    }

    public static function primaryKey()
    {
        return ['variable_id', 'user_id', 'site_id', 'page_id'];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variable_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variable_id'], 'required'],
            [['variable_id'], 'integer'],
            [
                ['variable_id', 'user_id', 'site_id', 'page_id'], 'unique',
                'targetAttribute' => ['variable_id', 'user_id', 'site_id', 'page_id'],
                'message' => Yii::t('app',
                    'The combination of Variable, User, Site and Page has already been taken.')
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'variable_id' => Yii::t('app', 'Variable'),
            'user_id' => Yii::t('app', 'User'),
            'user.username' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'site.title' => Yii::t('app', 'Website'),
            'page_id' => Yii::t('app', 'Page'),
            'page.title' => Yii::t('app', 'Page'),
            'value_data' => Yii::t('app', 'Value Data'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

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
     * @return \yii\db\ActiveQuery
     */
    public function getVariable()
    {
        return $this->hasOne(Variable::className(), ['id' => 'variable_id']);
    }

    public function setValue($value)
    {
        $this->value_data = $value;
    }

    public function getValue()
    {
        switch ($this->variable->type_id) {
            case 0:
            default:
                return strval($this->value_data);
        }
    }

}
