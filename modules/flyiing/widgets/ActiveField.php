<?php

namespace flyiing\widgets;

use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveField as BaseActiveField;

/**
 * Расширенный класс для полей ввода.
 *
 * Добавлена возможность использовать `input-group` и `input-group-addon`(`input-group-btn`) из bootstrap 3,
 * что позволяет "прилеплять"(слева или справа) к полям ввода произвольный контент(кнопки, пометки и тд).
 * В дальнейшем, элемент такого контента будет, для простоты, будет именваться addon.
 *
 * Для определения addon'ов необходимо задать свойство [[inputGroup]]. Это массив, каждый элемент которого
 * должен быть либо массивом, либо строкой. Массивы с ключами `tag`, `size` и `options` не являются addon'ами,
 * а используются как параметры основной обёртки(`class="input-group"`).
 * Остальные массивы расцениваются как описания параметров addon'ов. Если не нужно указывать никаких
 * дополнительных параметров, то в качестве элемента [[inputGroup]] можно передать строку, и тогда она будет
 * расцениваться как содержимое addon'а.
 *
 * Параметр `type` может принимать значения 'prepend'(отображать слева от поля ввода) и 'append'(справа).
 * Как вариант, можно указывать `prepend-btn` и `append-btn`. В этом случае, обёртка будет иметь css-класс
 * `class="input-group-btn"`.
 *
 * Кроме того, можно указывать тип addon'а прямо в ключе элемента: если ключ - строка, и в ней содержаться
 * слова `append`, `prepend` или `btn`, то автоматически установятся соответсвующие параметры для addon'а.
 *
 * Пример использования:
 *
 * ~~~
 * echo $form->field($model, 'field', [
 *
 *   'inputGroup' => [ // список addon'ов и опций
 *
 *     '@', // можно указать просто строку, тогда по умолчанию она "прилипнет" слева
 *
 *     // параметры из ключа:
 *     'first-btn' => Button::widget(['label' => 'Кнопка слева']),
 *     'append-text' => '...', // текст справа
 *     'append-second-btn' => Button::widget(['label' => 'Кнопка справа']),
 *
 *     // Если нужно передать какие то дополнительные параметры(наприме доп.атрибуты тегов),
 *     // можно указать массив, и расписать всё в нём:
 *     [
 *       'content' => '...',
 *       // так же можно указать дополнительные(опциональные) параметры
 *       'type' => 'append-btn', // "прилепляем" справа
 *       'tag' => 'span', // тэг для обёртки content(`class="input-group-addon"`)
 *
 *        // всё остальное будет считаться доп.атрибутами этого тега и будет передано как $options в
 *        // функию [Html::tag()](http://www.yiiframework.com/doc-2.0/yii-helpers-basehtml.html#tag%28%29-detail)
 *       'class' => ... // по умолчанию `input-group-addon`
 *       'style' => ...
 *       'onclick' => ...
 *       ...
 *     ],
 *
 *     // элементы с ключами 'tag' и 'options' расцениваются как параметры(НЕ addon'ы)
 *     'tag' => 'div', // какой тег для обёртки(`class="input-group"`), по умолчанию div
 *     'size' => 'small', // `small` или `sm` - поменьше, `large` или `lg` - побольше
 *     'options' => [...], // дополнительные атрибуты этого тега, см. [Html::tag()](http://www.yiiframework.com/doc-2.0/yii-helpers-basehtml.html#tag%28%29-detail)
 *   ],
 * ])->textInput();
 * ~~~
 *
 * Альтернативные реализации `input-group`:
 * [kartik\widgets\ActiveField](https://github.com/kartik-v/yii2-widgets/blob/master/ActiveField.php) -
 * Почти то же, только можно задавать всего по одному addon'у слева и справа.
 *
 * Class ActiveField
 * @package flyiing\uni\widgets
 */
class ActiveField extends BaseActiveField
{

    /**
     * @var array Массив addon'ов(дополнительно контента) для отображения в поле ввода.
     */
    public $inputGroup = [];

    public function render($content = null)
    {
        if($content === null) {

            if(!empty($this->inputGroup))
                $this->parts['{input}'] = $this->renderInputGroup();

        }
        return parent::render($content);
    }

    /**
     * Эта функция ищет в строке, разделенной `-`, определения типа addon'а
     * и устанавливает соответсвующие значения массива параметров.
     *
     * @param $typeString
     * @param $addonOptions
     */
    public function applyAddonTypeString($typeString, &$addonOptions)
    {
        foreach(explode('-', $typeString) as $part) {
            switch($part) {
                case 'prepend':
                case 'append':
                    $addonOptions['type'] = $part;
                    break;
                case 'btn':
                    $addonOptions['_class'] = 'input-group-btn';
                    break;
            }
        }
    }

    /**
     * Функция renderInputGroup возвращает строку содержимого {input}, при этом,
     * если в свойстве [[inputGroup]] задано хоть что-то для вывода, то {input}
     * будет завёрнут в тег с классом `input-group`, и всё содержимое из [[inputGroup]],
     * будет добавлено туда же.
     *
     * @return string
     */
    public function renderInputGroup()
    {
        // На всякий случай :) Чтобы не менять свойство [[inputGroup]]
        $inputGroup = $this->inputGroup;

        // Собираем параметры и удаляем их из массива, ибо это НЕ addon'ы
        $inputGroupOptions = ArrayHelper::remove($inputGroup, 'options', []);
        $inputGroupTag = ArrayHelper::remove($inputGroup, 'tag', 'div');
        $inputGroupClass = 'input-group';
        switch(ArrayHelper::remove($inputGroup, 'size', '')) {
            case 'lg':
            case 'large':
                $inputGroupClass .= ' input-group-lg';
                break;
            case 'sm':
            case 'small':
                $inputGroupClass .= ' input-group-sm';
                break;
        }

        // Вычисляем какой контент надо разместить ДО и ПОСЛЕ поля ввода.
        // Результат будет в $prepend и $append, соответсвенно.
        // Для этого пробегаемся по массиву addon'ов и собираем их контент в $prepend и $append,
        // в зависимости от указанных параметров для каждого addon'а.
        $prepend = '';
        $append = '';
        foreach($inputGroup as $addonKey => $addon) {

            // значения по умолчанию
            $addonContent = '';
            $addonOptions = [
                'type' => 'prepend',
                '_class' => 'input-group-addon',
            ];

            // если ключ - строка, то выдераем из неё возможные параметры
            if(is_string($addonKey))
                $this->applyAddonTypeString($addonKey, $addonOptions);

            if(is_string($addon)) {
                $addonContent = $addon;
            } elseif(is_array($addon)) {
                $addonOptions = ArrayHelper::merge($addonOptions, $addon);
                $this->applyAddonTypeString(ArrayHelper::remove($addonOptions, 'type', ''), $addonOptions);
                $addonContent = ArrayHelper::remove($addonOptions, 'content', '');
            }

            // Если содержимое для текущего элемента не пустое,
            // то добавляем его в $prepend или $append.
            if(strlen($addonContent) > 0) {
                Html::addCssClass($addonOptions, ArrayHelper::remove($addonOptions, '_class'));
                $addonType = ArrayHelper::remove($addonOptions, 'type', 'prepend');
                $addonTag = ArrayHelper::remove($addonOptions, 'tag', 'span');
                $addonContent = Html::tag($addonTag, $addonContent, $addonOptions);
                if($addonType == 'append')
                    $append .= $addonContent;
                else
                    $prepend .= $addonContent;
            }

        }

        // Если в результате имеем не пустые $prepend или $append,
        // то подменяем значение {input}, заворачивая его в тег класса `input-group*`
        if(strlen($prepend . $append) > 0) {
            $input = isset($this->parts['{input}']) ? $this->parts['{input}'] :
                Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
            Html::addCssClass($inputGroupOptions, $inputGroupClass);

            // заворачиваем всё в обёртку и возвращаем результат
            return Html::tag($inputGroupTag, $prepend . $input . $append, $inputGroupOptions);
        }

        // если ничего не получилось, то возвращаем обычный {input}
        return $this->parts['{input}'];
    }

}