<?php

return [

    'id'         => 'gmcf',

    'bootstrap'  => [
        'flyiing\translation\Bootstrap',
        'log',
        'user',
    ],

    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableConfirmation' => true,
            'modelMap' => [
                'User' => 'app\models\User',
                'Profile' => 'app\models\Profile',
            ],
            'admins' => ['admin'],
        ],
        'translation' => [
            'class' => 'flyiing\translation\Module',
        ],
    ],

    'components' => [

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'localhost',
            ],
            'useFileTransport' => false,
        ],

        'formatter' => [
//            'dateFormat' => 'php:d-M*Y',
            'dateFormat' => 'dd-MM-y',
            'datetimeFormat' => 'php:d-M-Y H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],

        'i18n' => [
            'class' => \flyiing\translation\I18N::className(),
//            'translations' => [
//                '*' => [
//                    'class' => 'yii\i18n\DbMessageSource',
//                    'sourceLanguage' => 'en-US',
//                    'basePath' => '@app/messages'
//                ],
//            ],
        ],

        'cache'        => [
            'class' => 'yii\caching\FileCache',
        ],

        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

    ],
];
