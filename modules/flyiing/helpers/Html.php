<?php

namespace flyiing\helpers;

use Yii;
use yii\bootstrap\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Button;

class Html extends \yii\helpers\Html {

    public static function icon($name, $p = null)
    {
        return Icon::show($name, $p);
    }

    public static function actions($actions, $reverse = false)
    {
        if (!is_array($actions)) {
            return '';
        }
        if ($reverse) {
            $actions = array_reverse($actions);
        }
        $rItems = [];
        foreach ($actions as $action => $config) {
            if (is_string($config)) {
                $rItems[] = $config;
                continue;
            }
            $disabled = ArrayHelper::getValue($config, 'options.disabled') === 'disabled';
            if (($url = ArrayHelper::remove($config, 'url')) && !$disabled) {
                $config['tagName'] = 'a';
                $config['options']['href'] = Url::to($url);
            }
            if ($icon = ArrayHelper::remove($config, 'icon')) {
                $config['encodeLabel'] = false;
                $config['label'] = Html::icon($icon) . ArrayHelper::getValue($config, 'label', '');
            }
            if (!ArrayHelper::getValue($config, 'options.class')) {
                $config['options']['class'] = 'btn-default';
            }
            $rItems[] = Button::widget($config);
        }
        return implode(' ', $rItems);
    }

    public static function buttonGroup($config)
    {
        $buttons = ArrayHelper::getValue($config, 'buttons', []);
        $defaultButton = ArrayHelper::remove($buttons, '__default__', []);
        foreach ($buttons as $k => $v) {
            $btn = ArrayHelper::merge($defaultButton, $v);
            $disabled = ArrayHelper::getValue($btn, 'options.disabled') === 'disabled';
            if (($url = ArrayHelper::remove($btn, 'url')) && !$disabled) {
                $btn['tagName'] = 'a';
                $btn['options']['href'] = Url::to($url);
            }
            if ($icon = ArrayHelper::remove($btn, 'icon')) {
                $btn['label'] = Html::icon($icon) . $btn['label'];
            }
            $buttons[$k] = $btn;
        }
        $config['buttons'] = $buttons;
        return ButtonGroup::widget($config);
    }

    public static function gsCols($columns, $options = [])
    {
        $result = '';
        $width = ArrayHelper::getValue($options, 'width', 12);
        $count = count($columns);
        $colWidth = floor($width / $count);
        foreach ($columns as $column) {
            $result .= Html::tag('div', $column, ['class' => 'col-md-' . $colWidth]);
        }
        return $result;
    }

}

