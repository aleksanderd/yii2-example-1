<?php

namespace app\models;

use flyiing\helpers\Html;
use Yii;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;

/**
 * Класс VariableModel расширяет класс [[DynamicModel]] позволяя сохранять данные в базу.
 * Для этого используется таблица {{%variable_value}} и соответсвующая модель [[VariableValue]].
 * Таким образом, каждое свойство в модели VariableModel соответсвует одному экземпляру [[VariableValue]],
 * то есть одной записи в таблице {{%variable_value}}. Не пишутся только свойства: user_id, site_id, page_id -
 * они служат основным ключем, совместно с `variable_id` - ссылкой на имя переменной.
 *
 * @property-read string $name Базовое имя всех переменных модели
 * @property integer|null $user_id Идентификатор пользователя
 * @property integer|null $site_id Идентификатор сайта
 * @property integer|null $page_id Идентификатор страницы
 * @property User|null $user Пользователь
 * @property Variable[] $variables Массив переменных Variable
 */
class VariableModel extends DynamicModel
{

    /**
     * @var integer|null  Идентификатор пользователя-владельца переменной.
     * Если null - системные переменные. Если -1 - все.
     */
    protected $_owner_id = null;

    /**
     * @var string Базовое имя для имен переменных. Используются только те переменные, имена
     * которых начинаются с этой строки плюс `.` (символ точки). Например, если базовое имя:
     * `u.settings`, то в модель загрузятся все переменные, такие как `u.settings.language`,
     * `u.settings.timezone` и т.д.
     */
    protected $_name = '';

    protected $_nameLength = 0;

    protected $_classes = [];

    protected $_autoload = true;

    /** @var null|VariableModel  */
    protected $_parent = false;

    protected $_parentField = false;

    /**
     * @var array Внутренний массив соответствия (коротких) имен идентификаторам переменных
     */
    private $_name2id = [];

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Variable'),
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'page_id' => Yii::t('app', 'Page'),
        ];
    }

    /**
     * Возвращает список имён атрибутов(переменных), которые позволено менять только админам.
     *
     * @return array
     */
    public function adminAttributes()
    {
        return [];
    }

    /**
     * Конструктор. Кроме всего прочего, можно задать свойства `owner_id`, `name` и `attributes`.
     *
     * @param array|string $config
     */
    public function __construct($config = [])
    {
        if (is_string($config)) {
            $config = ['name' => $config];
        }


        //printf('VM: %s construct %s' . PHP_EOL, $this->className(), $config['name']);
        $attributes = [];
        foreach (['user_id', 'site_id', 'page_id'] as $p) {
            if (isset($config[$p]) && intval($config[$p]) != 0) {
                $v = intval($config[$p]);
            } else {
                $v = null;
            }
            $attributes[$p] = $v;
        }
        $this->_owner_id = ArrayHelper::remove($config, 'owner_id', null);
        $this->_name = ArrayHelper::remove($config, 'name', '');
        $this->_parent = ArrayHelper::remove($config, '_parent', null);
        if ($this->_parent !== null) {
            $this->_parentField = $this->_name;
            $this->_name = $this->_parent->name .'.'. $this->_parentField;
        }
        $this->_autoload = ArrayHelper::remove($config, '_autoload', $this->_parent === null);
        $this->_nameLength = strlen($this->_name);

        // Применяем жестко заданные свойства в параметре конфига `attributes`
        foreach (ArrayHelper::remove($config, 'attributes', []) as $name => $value) {
            if (is_integer($name)) {
                $name = $value;
                $value = null;
            }
            if (!isset($attributes[$name])) {
                $attributes[$name] = $value;
            }
        }

        foreach ($this->_classes as $name => $class) {
            if (!class_exists($class) || in_array($name, $attributes) || isset($attributes[$name])) {
                continue;
            }
            $attributes[] = $name;
        }

        parent::__construct($attributes, $config);

        foreach ($this->_classes as $name => $class) {
            if (!class_exists($class)) {
                continue;
            }
            $this->addRule($name, 'safe'); // ?
            if (!$this->{$name} instanceof $class) {
                // ??? Мб излишняя проверка ???
                $cfg = is_array($this->{$name}) ? $this->{$name} : [];
                $this->{$name} = new $class(ArrayHelper::merge($cfg, [
                    '_parent' => $this,
                    'name' => $name,
                ]));
            }
        }

    }

    public function formName()
    {
        if ($this->_parent !== null) {
            return Html::getInputName($this->_parent, $this->_parentField);
        }
        return parent::formName();
    }

    public function init()
    {
        if ($this->_parent !== null) {
            $this->user_id = $this->_parent->user_id;
            $this->site_id = $this->_parent->site_id;
            $this->page_id = $this->_parent->page_id;
        } else if ($this->_autoload) {
            $this->loadFromDb();
        }
    }

    public function __set($name, $value)
    {
        if ($value !== null && in_array($name, ['user_id', 'site_id', 'page_id']) && intval($value) == 0) {
            $value = null;
        }
        parent::__set($name, $value);
    }

    /**
     * Преобразует короткое имя в полное, добавляя в начало [[$this->_name]]
     * Например, если базовое имя - `u.settings`, то вызов с параметром `language`
     * вернёт строку `u.settings.language`.
     *
     * @param string $name Короткое имя
     * @return string Полное имя
     */
    public function short2full($name)
    {
        return $this->_nameLength > 0 ? $this->_name .'.'. $name : $name;
    }

    /**
     * Преобразует полное имя в короткое, убирая из начача [[$this->_name]]
     * Например, если базовое имя - `u.settings`, то вызов с параметром `u.settings.language`
     * вернёт строку `language`.
     *
     * @param string $name Полное имя
     * @return string Короткое имя
     */
    public function full2short($name)
    {
        if ($this->_nameLength > 0) {
            return substr($name, $this->_nameLength + 1);
        } else {
            return $name;
        }
    }

    /**
     * Возвращает свойство модели. Если значение ActiveQuery, то получаем результат и возращаем его,
     * так как это делает ActiveRecord.
     *
     * @param string $name Имя свойства
     * @return array|mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        if (($value = parent::__get($name)) instanceof ActiveQueryInterface) {
            /** @var ActiveQueryInterface $value */
            return $value->all();
        } else {
            return $value;
        }
    }

    /**
     * Возвращает базовое имя для переменных модели. Например `u.settings`.
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariables()
    {
        $query = Variable::find();
        if ($this->_owner_id !== -1) {
            $query->andwhere(['user_id' => $this->_owner_id]);
        }
        if (strlen($this->_name) > 0) {
            $query->andWhere(['like', 'name', $this->_name .'.'. '%', false]);
        }
        $query->orderBy(['name' => SORT_ASC]);
        return $query;
    }

    public function loadFromDb()
    {
        //printf('VM: %s loadFromDb %s' . PHP_EOL, $this->className(), $this->_name);
        $attributes = $this->attributes();
        $subModels = [];

        foreach ($this->_classes as $name => $class) {
            if (!$this->{$name} instanceof $class) {
                $this->{$name} = new $class([
                    '_parent' => $this,
                    'name' => $name,
                ]);
            }
            /** @var VariableModel $sm */
            $sm = & $this->{$name};
            $sm->loadFromDb();
            $subModels[$name] = true;
        }

        // Чтение доступных переменных и загрузка значений
        foreach ($this->variables as $variable) {
            $shortName = $this->full2short($variable->name);
            if (isset($subModels[$shortName])) {
                continue;
            }

            // Если короткое имя поля содержит точки, то берем только то, что до первой точки,
            // а остальное, подгрузятся как свойства дочерней модели.
            if (sizeof($parts = explode('.', $shortName)) > 1) {
                if (isset($subModels[$parts[0]])) {
                    continue;
                }
                $value = new VariableModel($this->_name .'.'. $parts[0]);
                $subModels[$parts[0]] = true;
            } else {
                /** @var VariableValue $varVal */
                $varVal = VariableValue::findOne([
                    'variable_id' => $variable->id,
                    'user_id' => $this->user_id,
                    'site_id' => $this->site_id,
                    'page_id' => $this->page_id,
                ]);
                $value = $varVal ? $varVal->value : null;
                $this->_name2id[$shortName] = $variable->id;
            }
            if ($value !== null && in_array($shortName, $attributes)) {
                $this->{$shortName} = $value;
            }
        }
        return true;
    }

    /**
     * Сохраняет данные в базу.
     */
    public function save()
    {
        $result = true;
        foreach (array_diff($this->attributes(), ['user_id', 'site_id', 'page_id']) as $attribute) {
            $value = $this->{$attribute};
            if ($value instanceof VariableModel) {
                $result = $result && $value->save();
            } else {
                $variable_id = isset($this->_name2id[$attribute]) ?
                    $this->_name2id[$attribute] : Variable::name2id($this->short2full($attribute));
                $result = Variable::sSet($variable_id, $value,
                        $this->user_id, $this->site_id, $this->page_id) && $result;
            }
        }
        return $result;
    }

    /**
     * Возвращает конечные значения (используя умолчания)
     *
     * @return array
     */
    public function getValues()
    {
        $result = [];
        foreach (array_diff($this->attributes(), ['user_id', 'site_id', 'page_id']) as $attribute) {
            if ($this->{$attribute} instanceof VariableModel) {
                $result[$attribute] = $this->{$attribute}->getValues();
            } else {
                if (isset($this->_name2id[$attribute])) {
                    $variable_id = $this->_name2id[$attribute];
                } else {
                    $variable_id = Variable::name2id($this->short2full($attribute));
                }
                $result[$attribute] = Variable::sGet($variable_id, $this->user_id, $this->site_id, $this->page_id);
            }
        }
        return $result;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    if ($this->$name instanceof Model) {
                        $this->$name->setAttributes($value, $safeOnly);
                    } else {
                        $this->$name = $value;
                    }
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }

    /**
     * Аналог print_r для модели.
     *
     * @param bool $print
     * @param string $tab
     * @return string
     */
    public function dump($print = false, $tab = '')
    {
        $result = $tab . "name => '{$this->_name}'\n";
        foreach ($this->attributes() as $attribute) {
            $result .= $tab . $attribute . ' => ';
            $value = $this->{$attribute};
            if ($value instanceof VariableModel) {
                $result .= PHP_EOL . $value->dump(false, $tab . '  ');
            } else {
                $result .= isset($value) ? print_r($value, true) : 'null';
            }
            $result .= PHP_EOL;
        }
        if ($print) {
            echo $result;
        }
        return $result;
    }

    public function getUser()
    {
        return User::findOne($this->user_id);
    }

}
