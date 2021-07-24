<?php

namespace flyiing\helpers;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Помогает отображать иконки, используя гибкую систему конфигов и алиасов.
 * По умолчанию используется фреймворк с названием `bsg` - стандартный от Bootstrap 3
 * Например:
 * ```php
 * echo Icon::show('play');
 * echo Icon::show('play', '{i}'); // Без обрамления пробелами
 * echo Icon::show('play', '{i}&nbsp;&nbsp;&nbsp;'); // Три сурьёзных пробела.
 * echo Icon::show('play', ['tag' => 'i']);
 * echo Icon::show('play', ['fw' => 'fa']);
 * ```
 *
 * При этом имя иконки может быть алиасом, которые задаются через параметр `map`. Так, в коде можно использовать
 * внутренние алиасы, а их соответсвия реальным именам иконок задать в конфиге приложения. Таким образом, в будущем,
 * можно будет легко сменить любую иконку исправив лишь один конфиг, вместо кучи вьюшек.
 *
 * В конфиге (`$app->params['flyiing']['icon']`) можно задать следющие параметры:
 *
 *   `fw` - название основного фреймфорка, по умолчанию - 'bsg'
 *   `template` - шаблон отображения готовой иконки, удобно для добавления пробелов и тд. по умолчнию - ' {i} '
 *   `classTemplate` - шаблон css-класса для иконки, напр. для 'bsg' - 'glyphicon glyphicon-{name}'
 *   `tag` - html-тэг, используемый для отображения иконки, по умолчанию - 'span'
 *   `map` - массив алиасов
 *
 * Дополнительный фреймворк можно определить через конфиг `$app->params['flyiing']['iconFrameworks']`.
 * Каждый элемент этого массив - конфиг(массив) фреймфорка. А ключ - его названием, напр. 'bsg'
 * В конфиге фреймфорка можно использовать все те же параметры что и в общем конфиге(кроме `fw`).
 * Параметр `map` для фреймфорка не переопределяется никакими настройками - это внутренние алиасы для этого фреймфорка.
 * Кроме того, в конфиге фреймворка можно задать имя класса для регистрации ресурсов(assets) - `assetClass`.
 *
 * Пример конфига:
 * ```php
 * $params['flyiing']['icon'] = [
 *   'tag' => 'i',
 *   'fw' => 'fa',
 *   'map' => [ ... ]
 * ];
 * $params['flyiing']['iconFrameworks'] = [
 *   'fa' => [
 *     'assetClass' => '\flyiing\helpers\FontAwesomeAsset',
 *     'classTemplate' => 'fa fa-{name} fa-fw',
 *     'map' => [ ... ]
 *   ],
 * ];
 * ```
 * @package flyiing\helpers
 */
class Icon {

    /**
     * @var array Массив конфигов доступных фреймворков
     */
    private static $_frameworks = null;

    /**
     * Возвращает массив конфигов всех доступных фреймворков.
     *
     * @param bool $force Если `true` - не использовать предыдущий результат
     * @return array
     */
    public static function getFrameworksConfig($force = false)
    {
        if (static::$_frameworks === null || $force) {
            $config = ArrayHelper::getValue(Yii::$app->params, 'flyiing.iconFrameworks', []);
            static::$_frameworks = ArrayHelper::merge([
                'bsg' => [
                    'assetClass' => '\yii\bootstrap\BootstrapAsset',
                    'classTemplate' => 'glyphicon glyphicon-{name}',
                    'map' => require(__DIR__ . '/defaults/iconMapBSG.php'),
                ],
                'fa' => [
                    'assetClass' => '\flyiing\assets\FontAwesomeAsset',
                    'classTemplate' => 'fa fa-{name} fa-fw',
                    'map' => require(__DIR__ . '/defaults/iconMapFA.php'),
                ],
            ], $config);
        }
        return static::$_frameworks;
    }

    /**
     * Возвращает конфиг заданного фреймфорка.
     *
     * @param string $fwName Название фреймворка, например 'bsg'
     * @return array Конфиг фреймфорка
     */
    public static function getFrameworkConfig($fw)
    {
        $fwsConfig = static::getFrameworksConfig();
        return isset($fwsConfig[$fw]) ? $fwsConfig[$fw] : [];
    }

    /**
     * Регистрирует нужный для текущего(или заданного) фреймфорка ассет.
     *
     * @param $view
     * @param string|null $fw
     * @return bool
     */
    public static function register($view, $fw = null)
    {
        $fwsConfig = static::getFrameworksConfig();
        if ($fw === null) {
            $fw = ArrayHelper::getValue(Yii::$app->params, 'flyiing.icon.fw', 'bsg');
        }
        if ($assetClass = ArrayHelper::getValue($fwsConfig, $fw . '.assetClass')) {
            Yii::$app->params['flyiing']['icon']['fw'] = $fw;
            return $assetClass::register($view);
        } else {
            // мб исключение?
            return false;
        }
    }

    /**
     * Возвращает строку html-кода иконки
     *
     * @param string $name Имя иконки.
     * @param array|string|null $p Если строка, то используется как `template`, если массив, то как конфиг
     * @return string html-код иконки
     */
    public static function show($name, $p = null)
    {
        // глобальные настройки
        $config = ArrayHelper::getValue(Yii::$app->params, 'flyiing.icon', []);

        if (is_array($p)) { // если параметр - массив, используем его как конфиг
            $config = ArrayHelper::merge($config, $p);
        } else if (is_string($p)) { // если параметр - строка, используем как шаблон иконки
            $config['template'] = $p;
        }

        // получаем конфиг фреймворка
        $fwConfig = static::getFrameworkConfig(ArrayHelper::remove($config, 'fw', 'bsg'));
        $fwMap = ArrayHelper::remove($fwConfig, 'map', []);

        $config = ArrayHelper::merge($fwConfig, $config);
        ArrayHelper::remove($config, 'assetClass', false);

        // преобразование алиаса
        $map = ArrayHelper::merge(require(__DIR__ . '/defaults/iconMap.php'), ArrayHelper::remove($config, 'map', []));
        $name = ArrayHelper::getValue($map, $name, $name);
        // преобразование алиаса из конфига фреймворка
        $name = ArrayHelper::getValue($fwMap, $name, $name);

        $tag = ArrayHelper::remove($config, 'tag', 'i');
        $template = ArrayHelper::remove($config, 'template', ' {i} ');
        $classTemplate = ArrayHelper::remove($config, 'classTemplate', '{name}');
        $config['class'] = str_replace('{name}', $name, $classTemplate);

        return str_replace('{i}', Html::tag($tag, '', $config), $template);
    }

}