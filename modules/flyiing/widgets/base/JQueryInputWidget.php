<?php

namespace flyiing\widgets\base;

use \yii\helpers\Json;

/**
 * Базовый класс для виджетов с использованием JQuery плагинов.
 */
class JQueryInputWidget extends \flyiing\widgets\base\InputWidget
{
    /**
     * @var array Массив опций для плагина
     */
    public $pluginOptions = [];

    /**
     * @var string Селектор элемента, к которому будет применен плагин.
     * Если не задано, будет использоваться селектор по id из [[options]].
     */
    public $selector;

    public function init()
    {
        parent::init();
        if(empty($this->selector))
            $this->selector = '#' . $this->options['id'];
    }

    public function registerPlugin($plugin)
    {
        $this->view->registerJs(sprintf('jQuery("%s").%s(%s)',
            $this->selector, $plugin, Json::encode($this->pluginOptions)));
    }

}
