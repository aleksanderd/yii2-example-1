<?php

namespace app\widgets;

use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Menu extends \yii\widgets\Menu {

    public $encodeLabels = false;

    public $linkTemplate = '<a href="{url}" {linkOptions}>{icon}{label}</a>';

    public $submenuTemplate = '<ul class="nav nav-second-level collapse">{items}</ul>';

    public $labelTemplate = '<a href="#" {linkOptions}>{icon}{label}</a>';

    public $activateParents = true;

    public function init()
    {
        Html::addCssClass($this->options, 'nav metismenu');
    }

    protected function calcLevels($items, $level = 0)
    {
        foreach ($items as $k => $v) {
            $items[$k]['level'] = ArrayHelper::getValue($v, 'level', $level);
            if (isset($v['items'])) {
                $items[$k]['items'] = $this->calcLevels($v['items'], $level + 1);
            }
        }
        return $items;
    }

    protected function renderItems($items)
    {
        return parent::renderItems($this->calcLevels($items));
    }

    /**
     * @param array $item
     * @return string
     */
    protected function renderItem($item)
    {
        if (isset($item['items'])) {
            // TODO нада бы врапперы (tagNmae, options, etc) а не жесткие шаблоны
            $item['template'] = '<a href="#">{icon}</span>{label}<span class="fa arrow"></span></a>';
        }
        $level = ArrayHelper::getValue($item, 'level', 0);
        if ($level < 1) {
            $item['label'] = Html::tag('span', $item['label'], ['class' => 'nav-label']);
        }
        $icon = ArrayHelper::getValue($item, 'icon', '');
        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
        $tokens = [
            '{linkOptions}' => Html::renderTagAttributes($linkOptions),
            '{label}' => $item['label'],
            '{icon}' => strlen($icon) > 0 ? Html::icon($icon) : '',
        ];
        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);
            $tokens['{url}'] = Html::encode(Url::to($item['url']));
        } else {
            $template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);
        }
        return strtr($template, $tokens);
    }

}
