<?php

namespace app\widgets\vw;

use app\models\Variable;
use kartik\checkbox\CheckboxX;
use Yii;
use flyiing\helpers\Html;
use flyiing\widgets\base\JQueryInputWidget as BaseWidget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * VariableWidget предназначен для отображения поля ввода значения переменной.
 * Из конфига принимается класс базового виджета и его конфиг: `inputClass` и `inputConfig`.
 * Помимо базового виджета отображается чекбокс и скрытое актуальное значение. Если чекбокс выключен,
 * то в основной виджет не активен и отображает родительское значение переменной (значение по умолчанию).
 * Если чекбокс влючен, то основной виджет активен, и позволяет переопределить значение переменной.
 */
class VariableWidget extends BaseWidget
{
    /** @var \app\models\VariableModel */
    public $model;

    public $user_id;

    public $site_id;

    public $page_id;

    /** @var string Имя класса базового виджета */
    public $inputClass;

    /** @var array Конфиг для базового виджета */
    public $inputConfig = [];

    /** @var string Имя переменной */
    public $variableName;

    public $default = null;

    public function init()
    {
        parent::init();
        if ($this->hasModel()) {
            if (!$this->model->hasMethod('short2full') && !isset($this->variableName)) {
                throw new InvalidConfigException('Use VariableModel or set variableName');
            }
            if (!isset($this->variableName)) {
                $this->variableName = $this->model->short2full($this->attribute);
            }
            foreach (['user_id', 'site_id', 'page_id'] as $p) {
                $this->{$p} = $this->model->{$p};
            }
        } else {
            if (!isset($this->variableName)) {
                $this->variableName = $this->name;
            }
        }
        if (!isset($this->default)) {
            $this->default = Variable::sGet($this->variableName,
                $this->user_id, $this->site_id, $this->page_id, true);
        }
        $this->inputConfig['id'] = $this->options['id'] . '-input';
        $this->inputConfig['name'] = 'vw_input_' . $this->name;
        $this->inputConfig['value'] = Variable::sGet($this->variableName,
            $this->user_id, $this->site_id, $this->page_id);

        $this->pluginOptions['defaultValue'] = $this->default;
        $this->pluginOptions['inputSelector'] = '#' . $this->inputConfig['id'];
        $this->pluginOptions['checkboxSelector'] = '#' . $this->options['id'] . '_checkbox';
        $this->pluginOptions['valueSelector'] = '#' . $this->options['id'] . '_value';
        VariableWidgetAsset::register($this->view);
    }

    public function run()
    {
        $content = Html::beginTag('div', $this->options);

        if (is_array($this->value)) {
            $this->value = implode(',', $this->value);
        }

        $content .= Html::hiddenInput($this->name, $this->value, [
            'id' => $this->options['id'] . '_value',
        ]);

        $content .= '<table class="variable-widget"><tbody><tr><td class="variable-checkbox">';

        if (isset($this->value) && strlen($this->value) > 0) {
            $cbv = 1;
        } else {
            $cbv = 0;
        }
        $content .= CheckboxX::widget([
            'id' => $this->options['id'] . '_checkbox',
            'name' => 'vw_checkbox_' . $this->name,
            'value' => $cbv,
            'pluginOptions' => [
                'threeState' => false,
                'size' => 'sm',
            ],
        ]);

        $content .= '</td><td class="variable-input">';

        Html::addCssClass($this->inputConfig, 'form-control');

        switch ($this->inputClass) {
            case 'input':
                $input = Html::input(
                    ArrayHelper::getValue($this->inputConfig, 'type', 'text'),
                    $this->inputConfig['name'],
                    $this->inputConfig['value'],
                    $this->inputConfig
                );
                break;
            case 'textInput':
            case 'passwordInput':
            case 'textarea':
                $class = $this->inputClass;
                $input = Html::$class(
                    $this->inputConfig['name'],
                    $this->inputConfig['value'],
                    $this->inputConfig
                );
                break;
            case 'listBox':
                $class = $this->inputClass;
                $value = $this->inputConfig['value'];
                if (count($p = explode(',', $value)) > 1) {
                    $value = $p;
                }
                $input = Html::$class(
                    $this->inputConfig['name'],
                    $value,
                    ArrayHelper::remove($this->inputConfig, 'items', []),
                    $this->inputConfig
                );
                break;
            default:
                /* @var $class \yii\base\Widget */
                $class = $this->inputClass;
                $input = $class::widget($this->inputConfig);
        }

//        $stdInputs = ['textInput', 'passwordInput', 'fileInput', 'textarea', 'dropDownList', 'listBox'];
//        if ($this->inputClass)

        $content .= $input;
        $content .= '</td></tr></tbody></table>';
        $content .= Html::endTag('div');

        $this->registerPlugin('VariableWidget');
        return $content;

    }

}
