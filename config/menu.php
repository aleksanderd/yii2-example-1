<?php

use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;

$mainMenu = [
    //['label' => 'Home', 'url' => ['/site/index']],
];

$supportMenuItem = [
    'icon' => 'support',
    'label' => '',
    'items' => [
        [
            'icon' => 'support',
            'label' => Yii::t('app', 'Request support'),
            'url' => ['/support/request/form'],
            'linkOptions' => ['target' => '_blank'],
        ],
    ],
];

if (Yii::$app->user->isGuest) {

    $userMenuItem = [
        'icon' => 'log-in',
        'label' => Yii::t('user', 'Login'),
        'url' => ['/user/security/login'],
    ];

} else {

    /** @var \app\models\User $user */
    $user = Yii::$app->user->identity;


    $settingsMenuItem = [
        'icon' => 'wrench',
        'label' => Yii::t('app', 'Settings'),
        'items' => [
            [
                'label' => Yii::t('app', 'Notifications'),
                'url' => ['/variable/form', 'name' => 'u-notify'],
            ],
            [
                'label' => Yii::t('app', 'Widget'),
//                'url' => ['/variable/form', 'name' => 'w-settings'],
                'url' => ['/variable/form', 'name' => 'w-options'],
            ],
            [
                'label' => Yii::t('app', 'Texts'),
                'url' => ['/variable/form', 'name' => 'w-texts'],
            ],
            [
                'label' => Yii::t('app', 'Calls'),
                'url' => ['/variable/form', 'name' => 'c-settings'],
            ],
            [
                'label' => Yii::t('app', 'Files'),
                'url' => ['/file/manager'],
            ],
            [
                'label' => Yii::t('app', 'Call info blacklist'),
                'url' => ['/black-call-info/index'],
            ],
        ],
    ];

    $partnerMenuItem = [
        'icon' => 'partner',
        'label' => Yii::t('app', 'Partner program'),
        'items' => [
            [
                'label' => Yii::t('app', 'My partner'),
                'url' => ['/user-referral/partner'],
                'visible' => isset($user->partner),
            ],
            [
                'label' => Yii::t('app', 'Referral urls'),
                'url' => ['/referral-url/index'],
            ],
            [
                'label' => Yii::t('app', 'Referral users'),
                'url' => ['/user-referral/index'],
            ],
//            [
//                'label' => Yii::t('app', 'Promo codes'),
//                'url' => ['/promocode/index'],
//            ],
            [
                'label' => Yii::t('app', 'Payouts'),
                'url' => ['/payout/index'],
            ],
//            [
//                'label' => Yii::t('app', 'Promo materials'),
//                'url' => '',
//            ],
        ],
    ];

    $mainMenu = array_merge($mainMenu, [
        [
            'icon' => 'dashboard',
            'label' => Yii::t('app', 'Dashboard'),
            'url' => ['/dashboard/index'],
        ],
        [
            'icon' => 'client-site',
            'label' => Yii::t('app', 'Websites'),
            'url' => ['/client-site/index'],
        ],
        [
            'icon' => 'client-page',
            'label' => Yii::t('app', 'Pages'),
            'url' => ['/client-page/index'],
        ],
        [
            'icon' => 'client-line',
            'label' => Yii::t('app', 'Phone lines'),
            'url' => ['/client-line/index'],
        ],
        [
            'icon' => 'client-rule',
            'label' => Yii::t('app', 'Rules'),
            'url' => ['/client-rule/index'],
        ],
        [
            'icon' => 'client-query',
            'label' => Yii::t('app', 'Calls'),
            'url' => ['/client-query/index'],
        ],
        [
            'icon' => 'line-chart',
            'label' => Yii::t('app', 'Analytics'),
            'url' => ['/conversion/index'],
        ],
        $settingsMenuItem,
    ]);
    if (ArrayHelper::getValue(Yii::$app->params, 'referrals')) {
        $mainMenu[] = $partnerMenuItem;
    }

    $fundsMenuItem = [
        'icon' => Html::icon(Yii::$app->currencyCode),
        'label' => sprintf('%01.02f', $user->balance),
        'items' => [
            [
                'label' => Yii::t('app', 'Add funds'),
                'url' => ['/payments/default/add-select'],
            ],
            [
                'icon' => 'tariff',
                'label' => Yii::t('app', 'Tariffs'),
                'url' => ['/user-tariff/index'],
            ],
            [
                'label' => Yii::t('app', 'Payments'),
                'url' => ['/payment/index'],
            ],
            [
                'label' => Yii::t('app', 'Transactions'),
                'url' => ['/transaction/index'],
            ],
        ],
    ];

    $userMenuItem = [
        'icon' => 'user',
        'label' => $user->username,
        'items' => [
            [
                //'icon' => 'user-profile',
                'label' => Yii::t('user', 'Profile'),
                'url' => ['/user/settings/profile'],
            ],
            [
                //'icon' => 'user-account',
                'label' => Yii::t('user', 'Account'),
                'url' => ['/user/settings/account'],
            ],
            /*
            [
            'icon'  => 'user-payement',
            'label' => Html::icon('user-payment') . Yii::t('app', 'Add payment'),
            'url'   => ['/payment/select'],
            ],
             */
            [
                //'icon' => 'user-settings',
                'label' => Yii::t('app', 'Settings'),
                'url' => ['/user/settings/settings'],
            ],
            [
                //'icon' => 'log-out',
                'label' => Yii::t('user', 'Logout'),
                'url' => ['/user/security/logout'],
                'linkOptions' => ['data-method' => 'post'],
            ],
        ],
    ];

    if ($user->isAdmin) {
        $adminMenuItem = [
            'icon' => 'admin',
            'label' => Yii::t('app', 'System'),
            'items' => [
                [
                    'icon' => 'user',
                    'label' => Yii::t('user', 'Users'),
                    'url' => ['/user/admin/index'],
                ],
                [
                    'icon' => 'price',
                    'label' => Yii::t('app', 'Price'),
                    'url' => ['/variable/form', 'name' => 's-price'],
                ],
                [
                    'icon' => 'tariff',
                    'label' => Yii::t('app', 'Tariffs'),
                    'url' => ['/tariff/index'],
                ],
                [
                    'icon' => 'tariff',
                    'label' => Yii::t('app', 'Admin tariffs'),
                    'url' => ['/user-tariff/admin'],
                ],
                [
                    'icon' => 'user-settings',
                    'label' => Yii::t('app', 'Users settings'),
                    'url' => ['/variable/form', 'name' => 'u-settings'],
                ],
                [
                    'icon' => 'system-settings',
                    'label' => Yii::t('app', 'System settings'),
                    'url' => ['/variable/form', 'name' => 's-settings'],
                ],
                [
                    'icon' => 'notification',
                    'label' => Yii::t('app', 'System notifications'),
                    'url' => ['/variable/form', 'name' => 's-notify'],
                ],
                [
                    'icon' => 'variable',
                    'label' => Yii::t('app', 'Variables'),
                    'url' => ['/variable/index'],
                ],
                [
                    'icon' => 'variable-value',
                    'label' => Yii::t('app', 'Variables values'),
                    'url' => ['/variable-value/index'],
                ],
                [
                    'icon' => 't-message',
                    'label' => Yii::t('app', 'Translations'),
                    'url' => ['/translation/message'],
                ],
            ],
        ];
    }
}

$secondMenu = [
    'user' => $userMenuItem,
//    'support' => $supportMenuItem,
];

if (isset($fundsMenuItem) && ArrayHelper::getValue(Yii::$app->params, 'funds', true)) {
    $secondMenu = array_merge([$fundsMenuItem], $secondMenu);
}

//if (isset($settingsMenuItem)) {
//    $secondMenu = array_merge([$settingsMenuItem], $secondMenu);
//}

if (isset($adminMenuItem)) {
    $secondMenu = array_merge([$adminMenuItem], $secondMenu);
}

$mainMenu[] = [
    'icon' => 'support',
    'label' => Yii::t('app', 'Support'),
    'url' => ['/s-ticket/index'],
];

return [
    'main' => $mainMenu,
    'second' => $secondMenu,
];
