<?php

namespace app\widgets\time;

class TimeWidgetAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@app/widgets/time/assets';

    public $css = [
        'time-widget.css',
    ];

    public $js = [
        'time-widget.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
