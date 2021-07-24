<?php

use yii\helpers\FileHelper;

// нужно для новых данных по таймзонам
putenv('ICU_DATA=/opt/icu/');

Yii::setAlias('@flyiing', FileHelper::normalizePath(__DIR__ . '/../modules/flyiing'));

$return = [

    'flyiing' => [
        'icon' => [
            'fw' => 'fa',
            'map' => [
                'run' => 'send',
                'client-page' => 'list',
                'client-line' => 'phone',
                'client-rule' => 'filter',
                'client-query' => 'retweet',
                'user-profile' => 'briefcase',

                'USD' => 'usd',
                'RUB' => 'rouble',
            ],
        ],
        'iconFrameworks' => [
            'bsg' => [
                'map' => [
                    'client-site'       => 'modal-window',
                    'client-query-test' => 'screenshot',
                    'variable' => 'option-vertical',
                    'variable-value' => 'option-vertical',
                ],
            ],
            'fa'  => [
                'map' => [
                    'log-in'            => 'sign-in',
                    'log-out'           => 'sign-out',
                    'client-site'       => 'building',
//                    'client-site' => 'newspaper-o',
                    'client-query-test' => 'bug',
                    'variable' => 'ellipsis-v',
                    'variable-value' => 'ellipsis-v',
                    'tariff-activate' => 'long-arrow-up',
                    'tariff-deactivate' => 'long-arrow-down',
                    'tariff-status-active' => 'check',
                    'tariff-status-renew' => 'times',
                    'tariff-status-draft' => 'sticky-note-o',
                    'tariff-status-finished' => 'battery-empty',
                    'tariff-status-ready' => 'battery-full',
                    'analytics' => 'area-chart',
                    'partner' => 'share-alt',
                    'payout-reject' => 'remove',
                    'payout-start' => 'play-circle',
                    'payout-complete' => 'check',
                    'payout-process' => 'hourglass-half',
                    'payout-request' => 'hourglass-start',
                    'payout-retry' => 'clone',
                    'payment-completed' => 'check',
                    'payment-canceled' => 'remove',
                    'payment-error' => 'error',
                    'modal-text' => 'list-alt',
                    'sticket' => 'support',
                ],
            ],
        ],
    ],

    'adminEmail' => 'admin@example.com',

//    'yaKassa'    => [
//        'requestURL' => 'https://demomoney.yandex.ru/eshop.xml',
//        'params'     => [
//            'shopId' => '42877', // Идентификатор Контрагента, выдается Оператором.
//            'scid'   => '63019', // Номер витрины Контрагента, выдается Оператором.
//        ],
//    ],

    'widgetsDefaults' => [
        'GridView' => [
//            'bordered' => false,
            'striped' => false,
            'hover' => true,
            'responsive' => false,
        ],
    ],

    'cssAnimations' => [
        'bounce',
        'flash',
        'pulse',
        'rubberBand',
        'shake',
        'swing',
        'tada',
        'wobble',
        'jello',
        'bounceIn',
        'bounceInDown',
        'bounceInLeft',
        'bounceInRight',
        'bounceInUp',
        /*
        'bounceOut',
        'bounceOutDown',
        'bounceOutLeft',
        'bounceOutRight',
        'bounceOutUp',
        */
        'fadeIn',
        'fadeInDown',
        'fadeInDownBig',
        'fadeInLeft',
        'fadeInLeftBig',
        'fadeInRight',
        'fadeInRightBig',
        'fadeInUp',
        'fadeInUpBig',
        /*
        'fadeOut',
        'fadeOutDown',
        'fadeOutDownBig',
        'fadeOutLeft',
        'fadeOutLeftBig',
        'fadeOutRight',
        'fadeOutRightBig',
        'fadeOutUp',
        'fadeOutUpBig',
        */
        'flipInX',
        'flipInY',
        //'flipOutX',
        //'flipOutY',
        'lightSpeedIn',
        //'lightSpeedOut',
        'rotateIn',
        'rotateInDownLeft',
        'rotateInDownRight',
        'rotateInUpLeft',
        'rotateInUpRight',
        /*
        'rotateOut',
        'rotateOutDownLeft',
        'rotateOutDownRight',
        'rotateOutUpLeft',
        'rotateOutUpRight',
        */
        //'hinge',
//        'rollIn',
        //'rollOut',
        'zoomIn',
        'zoomInDown',
        'zoomInLeft',
        'zoomInRight',
        'zoomInUp',
        /*
        'zoomOut',
        'zoomOutDown',
        'zoomOutLeft',
        'zoomOutRight',
        'zoomOutUp',
        */
        'slideInDown',
        'slideInLeft',
        'slideInRight',
        'slideInUp',
        /*
        'slideOutDown',
        'slideOutLeft',
        'slideOutRight',
        'slideOutUp',
        */
    ],

];

return \yii\helpers\ArrayHelper::merge($return, require(__DIR__ . '/params.local.php'));