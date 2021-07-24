<?php

use yii\helpers\ArrayHelper;

Yii::setAlias('@webroot', __DIR__ . '/../');
Yii::setAlias('@web', '/');
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');

$config = [
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'app\commands',
    'modules' => [
    ],
    'components' => [
    ],
    'params' => $params,
];

return ArrayHelper::merge(require(__DIR__ .'/common.php'),
    ArrayHelper::merge($config,
        require(__DIR__ .'/local.php')));
