<?php
/**
 * Created by PhpStorm.
 * User: fly
 * Date: 24.04.15
 * Time: 0:58
 */

namespace app\assets;

use yii\web\AssetBundle;

class AnimateCssAsset extends AssetBundle {

    public $sourcePath = '@bower/animate.css';

    public $css = [
        'animate.min.css',
    ];
}
