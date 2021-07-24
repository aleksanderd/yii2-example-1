<?php

use yii\helpers\ArrayHelper;

$params = require __DIR__ . '/params.php';

$timezone = 'Europe/Moscow';
//$timezone = 'UTC';

$config = [
    //'layout' => 'main-top-navigation',
    'language' => 'ru',
    'timezone' => $timezone,
    'basePath' => dirname(__DIR__),
    'modules' => [
        'datecontrol' => [
            'class' => 'kartik\datecontrol\Module',
            'displayTimezone' => $timezone,
//            'displaySettings' => [
//                'date' => 'php:d-M-Y',
//                'time' => 'php:H:i:s',
//                'datetime' => 'php:d-M-Y H:i:s',
//            ],
            'saveTimezone' => 'UTC',
            'saveSettings' => [
                'date' => 'php:U',
                'time' => 'php:U',
                'datetime' => 'php:U',
            ],
            'autoWidget' => true,
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
        'uni' => [
            'class' => 'flyiing\uni\Module',
        ],
        'user' => [
            'modelMap' => [
                'RegistrationForm' => 'app\models\user\RegistrationForm',
                'UserSearch' => 'app\models\UserSearch',
                'User' => 'app\models\User',
            ],
            'controllerMap' => [
                'settings' => 'app\controllers\user\SettingsController',
                'security' => 'app\controllers\user\SecurityController',
                'registration' => 'app\controllers\user\RegistrationController',
                'recovery' => '\app\controllers\user\RecoveryController',
            ],
        ],
        'payments' => [
            //'class' => \app\modules\payment\Module::className(),
            'class' => 'app\modules\payments\Module',
        ],
        'support' => [
            'class' => 'app\modules\support\Module',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'PbWsNr1ki7jmp6i7YmIXUxT0mTBrnN57',
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'payment/yandex-check-order' => 'payments/yandex/check-order',
                'payment/yandex-payment-aviso' => 'payments/yandex/payment-aviso',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'view' => [
            'theme' => [
                'baseUrl' => '@app/themes/inspinia',
                'pathMap' => [
                    '@app/views' => '@app/themes/inspinia/views',
                    '@dektrium/user/views' => '@app/views/user',
                ],
            ],
        ],
        'notification' => [
            'class' => 'jet\notifications\Notification',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'flyiing\giim\model\Generator',
            'flyiing\giim\crud\Generator',
        ],
    ];
}

return ArrayHelper::merge(require (__DIR__ . '/common.php'),
    ArrayHelper::merge($config,
        require (__DIR__ . '/local.php')));
