<?php

namespace app\widgets\vw;

class VariableWidgetAsset extends \yii\web\AssetBundle {

    public $sourcePath = '@app/widgets/vw/assets';

    public $css = [
        'variable-widget.css',
    ];

    public $js = [
        'variable-widget.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
