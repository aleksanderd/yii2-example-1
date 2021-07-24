<?php

namespace app\widgets;

use app\widgets\vw\VariableWidget;

/**
 * Класс VariableField автоматически "заворачивает" поле в виджет [[\app\widgets\vw\VariableWidget]].
 */
class VariableField extends ActiveField
{

    /** @var \app\models\VariableModel */
    public $model;

    public function render($content = null)
    {
        /** @var \app\models\User $user */
        $user = \Yii::$app->user->identity;
        if (!$user->isAdmin && in_array($this->attribute, $this->model->adminAttributes())) {
            return '';
        }
        return parent::render($content);
    }

    public function input($type, $options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = VariableWidget::widget([
            'model' => $this->model,
            'attribute' => $this->attribute,
            'inputClass' => 'input',
            'inputConfig' => [
                'type' => 'text',
                'inputConfig' => $options,
            ],
        ]);
        return $this;
    }

    public function textarea($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = VariableWidget::widget([
            'model' => $this->model,
            'attribute' => $this->attribute,
            'inputClass' => 'textarea',
            'inputConfig' => $options,
        ]);

        return $this;
    }

    public function listBox($items, $options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        $this->adjustLabelFor($options);
        $options['items'] = $items;
        $this->parts['{input}'] = VariableWidget::widget([
            'model' => $this->model,
            'attribute' => $this->attribute,
            'inputClass' => 'listBox',
            'inputConfig' => $options,
        ]);

        return $this;
    }

    public function widget($class, $config = [])
    {
        return parent::widget(VariableWidget::className(), [
            'inputClass' => $class,
            'inputConfig' => $config,
        ]);
    }

}
