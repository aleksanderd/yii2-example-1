<?php

namespace flyiing\widgets;

use kartik\helpers\Html;
use Yii;
use yii\bootstrap\Button;
use yii\helpers\ArrayHelper;

class ActiveForm extends \yii\bootstrap\ActiveForm
{

    public $fieldClass = 'flyiing\widgets\ActiveField';

    public $fieldConfig = [
        // скопировано из yii\bootstrap\ActiveField
        'horizontalCssClasses' => [
            'offset' => 'col-sm-offset-3',
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-6',
            'error' => '',
            'hint' => 'col-sm-3',
        ],
    ];

    public $options = ['role' => 'form'];

    public $layout = 'horizontal';

    public function buttons($configs = null, $options = [])
    {
        $defaultButtons = [
            'submit' => [
                'label' => Yii::t('app', 'Submit'),
                'options' => [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                ],
            ],
            'reset' => [
                'label' => Yii::t('app', 'Reset'),
                'options' => [
                    'type' => 'reset',
                    'class' => 'btn btn-warning',
                ],
            ],
        ];
        if ($configs === null) {
            $configs = ['submit'];
        }
        Html::addCssClass($options, 'form-group');
        $content = Html::beginTag('div', $options);
        if ($this->layout == 'horizontal') {
            $class = '';
            $class .= ' ' . ArrayHelper::getValue($this->fieldConfig, 'horizontalCssClasses.offset', 'col-sm-offset-3');
            $class .= ' ' . ArrayHelper::getValue($this->fieldConfig, 'horizontalCssClasses.wrapper', 'col-sm-6');
            $content .= Html::beginTag('div', ['class' => $class]);
        }
        // отрисовка кнопок
        foreach ($configs as $key => $btnConfig) {
            if (is_string($btnConfig)) {
                // если строка, то это сокращение типа 'submit' или 'reset' - берем стандартный конфиг
                if (!($btnConfig = ArrayHelper::getValue($defaultButtons, $btnConfig))) {
                    // иначе, строка как есть
                    $content .= ' ' . $btnConfig;
                    continue;
                }
            } else if (is_string($key) && ($defConfig = ArrayHelper::getValue($defaultButtons, $key))) {
                // если ключ - строка и такой есть в массиве конфигов стандартных кнопок, то
                // сливаем стандартный конфиг с переданным
                $btnConfig = ArrayHelper::merge($defConfig, $btnConfig);
            }
            $content .= ' ' . Button::widget($btnConfig);
        }
        if ($this->layout == 'horizontal') {
            $content .= '</div>';
        }
        $content .= Html::endTag('div'); // form-group
        return $content;

    }

}