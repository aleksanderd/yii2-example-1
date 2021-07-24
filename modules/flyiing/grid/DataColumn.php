<?php

namespace flyiing\grid;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class DataColumn
 * @package flyiing\grid
 *
 * Renders several attributes combined in one grid column
 * Отображение столбца содержащего несколько атрибутов модели
 */

class DataColumn extends \kartik\grid\DataColumn
{

    /**
     * @var array
     * List of attributes to combine
     * Simple format: [ 'attr1_name', 'attr2_name', ... ]
     * Extended format:
     * [
     *     'attr1_name' => [ // attribute name as item key
     *          'label' => 'attr1_label',
     *          'format' => 'attr1_format',
     *          ... // any property of the base DataColumn class possible here...
     *     ],
     *     [
     *          'attribute' => 'attr2_name', // attribute name as item property
     *          ...
     *     ],
     * ]
     */
    public $attributes = null;

    /**
     * @var string
     * Template for data cell in format: '...{attr1_name}...{attr2_name}...{attrN_name}...'
     */
    public $template = null;
    /**
     * @var string
     * Template for header cell in same format ^^^
     * If null, $template will be used
     */
    public $headerTemplate = null;

    public $filterTemplate = null;

    /**
     * @var array
     * backup variable to store base/default properties
     * переменная для сохранения изначальных опций в процессе перебора(при выводе) всех атрибутов
     */
    private $_configBackup = null;

    public function init()
    {
        parent::init();

        // setting up default templates if empty
        if ($this->template === null) {
            $this->template = '';
            foreach ($this->attributes as $key => $value) {
                $attr = is_string($value) ? $value : ArrayHelper::getValue($value, 'attribute', $key);
                $this->template .= '{' . $attr . '}<br>';
            }
            $this->template = substr($this->template, 0, -4);
        }
        if ($this->headerTemplate === null) {
            $this->headerTemplate = $this->template;
        }
        if ($this->filterTemplate === null) {
            $this->filterTemplate = $this->template;
        }

        $this->_configBackup = \yii\helpers\ArrayHelper::toArray($this, [], false);
    }

    /**
     * @param string $key item key
     * @param string $value item value
     *
     * Функция переконфигурации объекта(самого себя) в соответствии с
     * переданными $key и $value. Как минимум задает имя атрибута.
     */
    public function applyItemConfig($key, $value)
    {
        if(is_array($value)) {
            if(is_string($key))
                $this->attribute = $key;
            \Yii::configure($this, $value);
        } elseif(is_string($value))
            $this->attribute = $value;
    }

    protected function renderHeaderCellContent()
    {
        if($this->attributes === null)
            return parent::renderHeaderCellContent();

        $items = [];
        foreach($this->attributes as $key => $value) {
            $this->applyItemConfig($key, $value);
            $items['{' . $this->attribute . '}'] = parent::renderHeaderCellContent();
            \Yii::configure($this, $this->_configBackup);
        }
        return strtr($this->headerTemplate, $items);
    }

    protected function renderFilterCellContent()
    {
        if($this->attributes === null || isset($this->filter))
            return parent::renderFilterCellContent();

        $items = [];
        foreach($this->attributes as $key => $value) {
            $this->applyItemConfig($key, $value);
            $items['{' . $this->attribute . '}'] = parent::renderFilterCellContent();
            \Yii::configure($this, $this->_configBackup);
        }
        return strtr($this->filterTemplate, $items);
    }


    protected function renderDataCellContent($model, $key, $index)
    {
        if($this->attributes === null || $this->content !== null)
            return parent::renderDataCellContent($model, $key, $index);

        $items = [];
        foreach($this->attributes as $key => $value) {
            $this->applyItemConfig($key, $value);
            $items['{' . $this->attribute . '}'] = parent::renderDataCellContent($model, $key, $index);
            \Yii::configure($this, $this->_configBackup);
        }

        return strtr($this->template, $items);
    }

}
