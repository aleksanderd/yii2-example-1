<?php

namespace app\widgets\rl;

class RuleLineWidgetAsset extends \yii\web\AssetBundle {

    public $sourcePath = '@app/widgets/rl/assets';

    public $css = [
        'rule-line-widget.css',
    ];

    public $js = [
        'rule-line-widget.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
