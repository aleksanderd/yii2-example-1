<?php

namespace app\widgets\grid;

use flyiing\helpers\Html;
use Yii;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;

class DateTimeColumn extends DataColumn {

    public function __construct($config = [])
    {
        $attribute = ArrayHelper::getValue($config, 'attribute', 'at');
        $period = strtolower(ArrayHelper::remove($config, 'period', ''));
        $fmt = Yii::$app->formatter;

        if (strstr($period, 'year')) {
            $renderValue = function ($value) use ($fmt) {
                return Html::tag('span', $fmt->asDate($value, 'Y'), ['class' => 'big-bold']);
            };
        } else if (strstr($period, 'month')) {
            $renderValue = function ($value) use ($fmt) {
                return Html::tag('span', $fmt->asDate($value, 'LLLL Y'), ['class' => 'big-bold']);
            };
        } else if (strstr($period, 'day')) {
            $renderValue = function ($value) use ($fmt) {
                return Html::tag('span', $fmt->asDate($value, 'd MMMM Y'), ['class' => 'big-bold']);
            };
        } else if (strstr($period, 'hour')) {
            $renderValue = function ($value) use ($fmt) {
                $date = $fmt->asDate($value);
                $time = $fmt->asTime($value, 'HH:00') .' - '.
                    $fmt->asTime($value + 3600, 'HH:00');
                return Html::tag('span', $time, ['class' => 'big-bold']) .'<br>'. $date;
            };
        } else {
            $renderValue = function($value) use($fmt) {
                return '<strong>' . $fmt->asTime($value) .'</strong><br/>'. $fmt->asDate($value);
            };
        }

        parent::__construct(ArrayHelper::merge([
            'attribute' => $attribute,
            'content' => function($model) use ($attribute, $renderValue) {
                return $renderValue($model->{$attribute});
            },
            'hAlign' => 'center',
        ], $config));
    }

}
