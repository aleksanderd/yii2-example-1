<?php

namespace flyiing\grid;

use Yii;
use yii\bootstrap\Button;
use yii\helpers\ArrayHelper;
use flyiing\helpers\Html;

class ActionColumn extends \kartik\grid\ActionColumn {

    public $template = '<div class="btn-group btn-group-xs btn-group-vertical" role="group">{view}{update}{delete}</div>';
    public $width = 'auto';

    public static function renderDefaultButton($title, $url, $model, $key, $options = [])
    {
        $options = ArrayHelper::merge([
            'href' => $url,
            'class' => 'btn btn-default',
            'data-pjax' => 0,
        ], $options);
        return Button::widget([
            'tagName' => 'a',
            'encodeLabel' => false,
            'label' => $title,
            'options' => $options,
        ]);
    }

    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function($url, $model, $key) {
                return static::renderDefaultButton(Html::icon('model-view') . Yii::t('yii', 'View'), $url, $model, $key, [
                    'class' => 'btn btn-info',
                ]);
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function($url, $model, $key) {
                return static::renderDefaultButton(Html::icon('model-update') . Yii::t('yii', 'Update'), $url, $model, $key, [
                    'class' => 'btn btn-success',
                ]);
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function($url, $model, $key) {
                return static::renderDefaultButton(Html::icon('model-delete') . Yii::t('yii', 'Delete'), $url, $model, $key, [
                    'class' => 'btn btn-danger',
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    'data-method' => 'post',
                ]);
            };
        }
    }

}