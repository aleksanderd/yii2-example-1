<?php

namespace flyiing\assets;

use yii\web\AssetBundle;

/**
 * Class FontAwesomeAsset
 *
 * Для использования нужен пакет `bower-asset/font-awesome` (в секции `require` файла `composer.json`)
 *
 * @package flyiing\assets
 */
class FontAwesomeAsset extends AssetBundle {

    public $sourcePath = '@bower/font-awesome';

    public $css = [
        'css/font-awesome.min.css',
    ];
}
