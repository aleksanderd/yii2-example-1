<?php

namespace app\models;

use Yii;

/**
 * Модель для таблицы "{{%variable}}". Содержит информацию о системных(user_id == null) и
 * пользовательских переменных, такую как имя и тип. Сами значение переменных ханяться в таблице
 * "{{%variable_value}}" и модели [[VariableValue]].
 *
 * @property integer $id Идентификатор переменной
 * @property integer $user_id Идентификатор пользователя-владельца переменной. Если null - системная.
 * @property integer $type_id Идентификатор типа.
 * @property string $name Имя переменной.
 * @property string $options_data serialized значение $options.
 *
 * @property User $user
 * @property VariableValue[] $variableValues
 */
class Variable extends \yii\db\ActiveRecord
{

    const TYPE_RAW = 0;
    const TYPE_OBJECT = 100;

    /**
     * @var array Массив дополнительных опций
     */
    public $options;

    public function afterFind()
    {
        if (strlen($this->options_data) > 0) {
            $this->options = unserialize($this->options_data);
        }
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (isset($this->options)) {
            $this->options_data = serialize($this->options);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variable}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type_id'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [
                ['user_id', 'name'], 'unique',
                'targetAttribute' => ['user_id', 'name'],
                'message' => Yii::t('app',
                    'The combination of User and Name has already been taken.')
            ]
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
            'type_id' => Yii::t('app', 'Type'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public function getType()
    {
        switch ($this->type_id) {
            case 0:
            default:
                return 'string';
        }
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
    public function getVariableValues()
    {
        return $this->hasMany(VariableValue::className(), ['id' => 'id']);
    }

    /**
     * Возвращает идентификатор переменной по её имени.
     * Если такой переменной нет и $autoInsert = true, то автоматически добавляет.
     * @param string $name
     * @param int|null $user_id
     * @param bool $autoInsert
     * @return bool|int
     */
    public static function name2Id($name, $user_id = null, $autoInsert = true)
    {
        $config = compact('name', 'user_id');
        if (!($variable = Variable::findOne($config))) {
            if (!$autoInsert) {
                return false;
            }
            $variable = new Variable($config);
            if (!$variable->save()) {
                return false;
            }
        }
        return $variable->id;
    }

    /**
     * Устанавливает **системную**(variable.user_id=null) переменную. Если передано имя, и переменной с
     * таким именем не найдено, то она будет автоматически добавлена (см. [[Variable::name2id]]).
     * Если user_id === 'auto', тогда будет использован идентификатор текущего пользователя, null для гостя.
     *
     * @param string|array|int $variable_id Если целое - идентификатор переменной, если строка - имя.
     * @param mixed $value Значение переменной
     * @param integer|array|null $user_id Идентификатор пользователя. Если не null, то значение переменной
     * относится к настройкам пользователя, всем его сайтам, и всем их страницам
     * @param integer|null $site_id Идентификатор сайта. Если не null, то значение переменной относится
     * к конкретному, сайту пользователя и всем страницам сайта.
     * @param integer|null $page_id Идентификатор страницы. Если не null, то значение переменной относится
     * к странице заданного в `site_id` сайта.
     * @return bool|array true|false или массив с ошибками записи модели
     */
    public static function sSet($variable_id, $value, $user_id = null, $site_id = null, $page_id = null)
    {
        if (is_array($variable_id)) {
            $params = $variable_id;
            if (!isset($params['variable_id'])) {
                return null;
            }
            foreach (['variable_id', 'value', 'user_id', 'site_id', 'page_id', 'default'] as $p) {
                if (isset($params[$p])) {
                    ${$p} = $params[$p];
                }
            }
        }
        if (is_array($user_id)) {
            $params = $user_id;
            foreach (['user_id', 'site_id', 'page_id', 'default'] as $p) {
                if (isset($params[$p])) {
                    ${$p} = $params[$p];
                }
            }
        }
        if ($value === '') {
            $value = null;
        }
        if (is_string($variable_id) && !($variable_id = static::name2id($variable_id))) {
            return false;
        }
        if ($user_id === 'auto') {
            if (Yii::$app->user->isGuest) {
                $user_id = null;
            } else {
                $user_id = Yii::$app->user->id;
            }
        }
        $keyProps = ['variable_id', 'user_id', 'site_id', 'page_id'];
        foreach ($keyProps as $p) {
            if (${$p} !== null && intval(${$p}) < 1) {
                ${$p} = null;
            }
        }
        if ($variable_id === null) {
            return false;
        }

        $key = compact($keyProps);
        if (!($vv = VariableValue::findOne($key))) {
            if ($value === null) {
                return true;
            } else {
                $vv = new VariableValue($key);
            }
        }
        if ($value === null) {
            $dbRes = $vv->delete() === 1;
        } else {
            $vv->value = $value;
            $dbRes = $vv->save();
        }
        if ($dbRes === true) {
            return true;
        } else {
            return $vv->getErrors();
        }
    }

    /**
     * Возвращает экземпляр системной переменной по заданному идентификатору или имени, с учётом родительских значений.
     * Например, если задан пользователь, сайт и страница, но для страницы значение не задано, то
     * вернёт значение для всего сайта. Если и для сайта не задано, то вернёт значние для пользователя.
     * Если и пользователь не задан, то вернёт системное значение по умолчанию.
     * Если user_id === 'auto', тогда будет использован идентификатор текущего пользователя, null для гостя.
     *
     * @param integer|array|string $variable_id Идентификатор или имя переменной
     * @param integer|array|null $user_id Идентификатор пользователя
     * @param integer|null $site_id Идентификатор сайта
     * @param integer|null $page_id Идентификатор страницы
     * @param bool $default Получить родительское значение(по умолчанию).
     * @return VariableValue|null Экземпляр переменной
     */
    public static function sGetModel($variable_id, $user_id = null, $site_id = null, $page_id = null, $default = false)
    {
        if (is_array($variable_id)) {
            $params = $variable_id;
            if (!isset($params['variable_id'])) {
                return null;
            }
            foreach (['variable_id', 'user_id', 'site_id', 'page_id', 'default'] as $p) {
                if (isset($params[$p])) {
                    ${$p} = $params[$p];
                }
            }
        }
        if (is_array($user_id)) {
            $params = $user_id;
            foreach (['user_id', 'site_id', 'page_id', 'default'] as $p) {
                if (isset($params[$p])) {
                    ${$p} = $params[$p];
                }
            }
        }
        if (is_string($variable_id) && !($variable_id = static::name2id($variable_id, null, false))) {
            return null;
        }
        if ($user_id === 'auto') {
            if (!isset(Yii::$app->user) || Yii::$app->user->isGuest) {
                $user_id = null;
            } else {
                $user_id = Yii::$app->user->id;
            }
        }
        $keyProps = ['user_id', 'site_id', 'page_id'];
        foreach ($keyProps as $p) {
            if (${$p} !== null && intval(${$p}) < 1) {
                ${$p} = null;
            }
        }
        if ($default) {
            // заnullяем последний не-null из ключа, дабы получять родительское значение (по умолчанию)
            foreach (['page_id', 'site_id', 'user_id'] as $p) {
                if (${$p} !== null) {
                    ${$p} = null;
                    break;
                }
            }
        }
        $query = VariableValue::find()
            ->where(['variable_id' => $variable_id])
            ->limit(1)
            ->orderBy([
                'user_id' => SORT_DESC,
                'site_id' => SORT_DESC,
                'page_id' => SORT_DESC]
            );

        foreach (['user_id', 'site_id', 'page_id'] as $i) {
            if (isset(${$i}) && ${$i} > 0) {
                $query->andWhere(['OR', [$i => ${$i}],  [$i => null]]);
            } else {
                $query->andWhere([$i => null]);
            }
        }

        return $query->one();
    }

    /**
     * Отличается от [[sGetModel]] тем, что возвращает не [[VariableValue]], а **значение** переменной.
     *
     * @param integer|array|string $variable_id Идентификатор или имя переменной
     * @param integer|array|null $user_id Идентификатор пользователя
     * @param integer|null $site_id Идентификатор сайта
     * @param integer|null $page_id Идентификатор страницы
     * @param bool $default Получить родительское значение(по умолчанию).
     * @return mixed|null Значение переменной
     */
    public static function sGet($variable_id, $user_id = null, $site_id = null, $page_id = null, $default = false)
    {
        if ($model = static::sGetModel($variable_id, $user_id, $site_id, $page_id, $default)) {
            return $model->value;
        } else {
            return null;
        }
    }

}
