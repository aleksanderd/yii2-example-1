<?php

namespace flyiing\translation\models;

use Yii;
use yii\base\InvalidParamException;

/**
 * This is the model class for table "{{%t_message}}".
 *
 * @property integer $id
 * @property string $language
 * @property string $translation
 *
 * @property TSourceMessage $source
 */
class TMessage extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%t_message}}';
    }

    public static function primaryKey()
    {
        return ['id', 'language'];
    }

    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            [['id'], 'integer'],
            [['translation'], 'string'],
            [['language'], 'string', 'max' => 16]
        ];
    }

    public function attributeLabels()
    {
        $tAddon = isset($this->language) ? ' (' . $this->language . ')' : '';
        return [
            'id' => Yii::t('app', 'ID'),
            'language' => Yii::t('app', 'Language'),
            'translation' => Yii::t('app', 'Translation') . $tAddon,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(TSourceMessage::className(), ['id' => 'id']);
    }

    /**
     * Возвращает объект по заданному исх.сообщению и языку. Если `$translation` не `null` то перевод будет записан,
     * если его еще не было, или если `$overwrite` = `true`.
     *
     * @param integer|TSourceMessage $sourceMessage Исходное сообщение - объект [[TMessageSource]] или `id`
     * @param string $language Язык
     * @param string|null $translation Перевод
     * @param bool $overwrite Перезаписать значение
     * @return TMessage|null
     */
    public static function findOrCreate($sourceMessage, $language, $translation = null, $overwrite = false)
    {
        $id = $sourceMessage instanceof TSourceMessage ? $sourceMessage->id : intval($sourceMessage);
        $key = compact('id', 'language');
        if ($model = TMessage::findOne($key)) {

            if (isset($translation) &&
                (!isset($model->translation) || strlen($model->translation) < 1 || $overwrite)) {

                $model->translation = $translation;
                $model->save();
            }

        } else {
            $model = new TMessage(compact('id', 'language', 'translation'));
            $model->save();
        }
        return $model;
    }

}
