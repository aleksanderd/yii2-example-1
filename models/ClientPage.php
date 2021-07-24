<?php

namespace app\models;

use app\base\tplModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель для определения страницы или группы страниц. Таблица БД "{{%client_page}}".
 *
 * Для определения одной страницы сайта, достаточно указать тип - точное совпадение, и в поле `pattern` путь страницы.
 * Для определения группы страниц используются регулярные выражения.
 *
 * Основное: сам шаблон должен быть заключён в "разделители",
 * подробнее тут: http://php.net/manual/ru/regexp.reference.delimiters.php
 *
 * Примеры:
 *
 * - `/word/` => строка `word` в любом месте.
 * - `/^word/` => то же, только в строго начале строки.
 * - `/word$/` => то же, только в конце строки.
 * - `/^\/catalog/` => в начале строго `/catalog`: слеш, как и точка и многое другое
 * [экранируется](http://php.net/manual/ru/regexp.reference.escape.php) обратным слешем `\`.
 * - `/^\/(page1|page2)/` => или `/page1...` или `/page2...`
 * - `/^\/somepage.*\.html?/` => страницы начинающиеся с `/somepage` и заканчивающиеся на `.htm` или `.html`:
 * точка `.` - любой символ, `*` - 0 или более раз, `?` - 0 или 1 раз.
 * Подробднее [тут](http://php.net/manual/ru/regexp.reference.meta.php).
 * - `/^\/catalog\/(auto|moto|sport)\/discount/` => `/catalog/auto/discount...`, `/catalog/moto/discount...` и
 * `/catalog/sport/discount...`.
 *
 *
 * Проверка регулярноного выражения онлайн:
 * - https://ru.functions-online.com/preg_match.html
 * - https://regex101.com/
 *
 * Документация:
 * - http://php.net/manual/ru/reference.pcre.pattern.syntax.php
 * - http://habrahabr.ru/post/115825/
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $priority
 * @property string $title
 * @property integer $type
 * @property string $pattern Шаблон для сравнения пути урла. Либо =, либо рег.выражение.
 * @property string $options_data
 * @property array $options
 *
 * @property boolean $ignoreDomain
 * @property boolean $ignoreParams
 * @property ClientSite $site
 * @property ClientRule[] $clientRules
 * @property User $user
 * @property Variable[] $variables
 */
class ClientPage extends \yii\db\ActiveRecord
{

    use tplModel;

    public $options;

    /** Точное совпадение. */
    const TYPE_EXACT = 0;
//    const TYPE_MASK = 1;
    /** Регалярное выражение. */
    const TYPE_REGEX = 2;

    public static function getTypeLabels()
    {
        return [
            static::TYPE_EXACT => Yii::t('app', 'Exact match'),
            //static::TYPE_MASK => Yii::t('app', 'Simple mask'),
            static::TYPE_REGEX => Yii::t('app', 'Regular expression'),
        ];
    }

    public function getTypeLabel()
    {
        return ArrayHelper::getValue(
            static::getTypeLabels(),
            $this->type,
            Yii::t('app', 'Unknown pattern type')
        );

    }

    public function afterFind()
    {
        $this->options = unserialize($this->options_data);
    }

    public function beforeSave($insert)
    {
        if (is_array($this->options)) {
            $this->options_data = serialize($this->options);
        }
        return parent::beforeSave($insert);
    }

    public function getIgnoreParams()
    {
        return ArrayHelper::getValue($this->options, 'ignoreParams', false);
    }

    public function setIgnoreParams($value)
    {
        $this->options['ignoreParams'] = is_bool($value) ? $value : intval($value) > 0;
    }

    public function getIgnoreDomain()
    {
        return ArrayHelper::getValue($this->options, 'ignoreDomain', true);
    }

    public function setIgnoreDomain($value)
    {
        $this->options['ignoreDomain'] = is_bool($value) ? $value : intval($value) > 0;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_id', 'title', 'pattern', 'priority'], 'required'],
            [['user_id', 'site_id', 'type', 'priority'], 'integer'],
            [['title', 'pattern'], 'string', 'min' => 1, 'max' => 255],
            [['ignoreDomain', 'ignoreParams'], 'safe'],
            [
                ['user_id', 'site_id', 'type', 'title'], 'unique',
                'targetAttribute' => ['user_id', 'site_id', 'type', 'title'],
                'message' => 'The combination of User ID, Site ID, Type and Title has already been taken.'
            ],
            [
                ['user_id', 'site_id', 'type', 'pattern'], 'unique',
                'targetAttribute' => ['user_id', 'site_id', 'type', 'pattern'],
                'message' => 'The combination of User ID, Site ID, Type and Pattern has already been taken.'
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
            'site_id' => Yii::t('app', 'Website ID'),
            'priority' => Yii::t('app', 'Priority'),
            'title' => Yii::t('app', 'Title'),
            'type' => Yii::t('app', 'Pattern type'),
            'typeLabel' => Yii::t('app', 'Pattern type'),
            'pattern' => Yii::t('app', 'Page url pattern'),

            'ignoreDomain' => Yii::t('app', 'Ignore url domain'),
            'ignoreParams' => Yii::t('app', 'Ignore url parameters'),
        ];
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
    public function getClientRules()
    {
        return $this->hasMany(ClientRule::className(), ['page_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariables()
    {
        return $this->hasMany(Variable::className(), ['page_id' => 'id']);
    }

    public function testUrl($url)
    {
        $parts = parse_url($url);
        $data = $parts['path'];
        if (!$this->ignoreDomain && isset($parts['host'])) {
            $data = $parts['host'] . $data;
        }
        if (!$this->ignoreParams && isset($parts['query'])) {
            $data .= '?' . $parts['query'];
        }
        if ($this->type === static::TYPE_EXACT) {
            return $data === $this->pattern;
        } else if ($this->type === static::TYPE_REGEX) {
            try {
                return preg_match($this->pattern . 'iu', $data) === 1;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
