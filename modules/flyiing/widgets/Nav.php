<?php

namespace flyiing\widgets;

use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use flyiing\helpers\Html;

class Nav extends \yii\bootstrap\Nav {

    public $renderItem = null;

    public function renderItem($item)
    {
        if (is_callable($this->renderItem)) {
            $item = call_user_func($this->renderItem, $item);
        }
        if (is_string($item)) {
            return $item;
        }
        if (isset($item['icon'])) {
            $item['label'] = Html::icon($item['icon']) . ArrayHelper::getValue($item, 'label', '');
        }
        return parent::renderItem($item);
    }

}
