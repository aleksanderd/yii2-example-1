<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\widgets\Menu;

/** @var dektrium\user\models\User $user */
$user = Yii::$app->user->identity;
$networksVisible = count(Yii::$app->authClientCollection->clients) > 0;

$menu = Menu::widget([
    'options' => [
        'class' => 'nav nav-pills'
    ],
    'items' => [
        ['label' => Yii::t('user', 'Profile'),  'url' => ['/user/settings/profile']],
        ['label' => Yii::t('user', 'Account'),  'url' => ['/user/settings/account']],
        ['label' => Yii::t('app', 'Settings'),  'url' => ['/user/settings/settings']],
        ['label' => Yii::t('user', 'Networks'), 'url' => ['/user/settings/networks'], 'visible' => $networksVisible],
    ]
]);

echo $menu;
