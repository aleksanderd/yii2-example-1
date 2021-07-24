<?php

namespace app\widgets\hint;

use yii\web\AssetBundle;

class HintWidgetAsset extends AssetBundle {

    public $sourcePath = '@app/widgets/hint/assets';

    public $css = [
        'hint.css'
    ];

    public $js = [
        'hint.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
