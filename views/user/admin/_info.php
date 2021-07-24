<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

/**
 * @var yii\web\View
 * @var $user app\models\User
 */
use app\models\UserTariff;
use flyiing\helpers\Html;

?>

<?php $this->beginContent('@dektrium/user/views/admin/update.php', ['user' => $user]) ?>

<table class="table">
    <tr>
        <td><strong><?= Yii::t('user', 'Registration time') ?>:</strong></td>
        <td><?= Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$user->created_at]) ?></td>
    </tr>
    <?php if ($user->registration_ip !== null): ?>
        <tr>
            <td><strong><?= Yii::t('user', 'Registration IP') ?>:</strong></td>
            <td><?= $user->registration_ip ?></td>
        </tr>
    <?php endif ?>
    <tr>
        <td><strong><?= Yii::t('user', 'Confirmation status') ?>:</strong></td>
        <?php if ($user->isConfirmed): ?>
            <td class="text-success"><?= Yii::t('user', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [$user->confirmed_at]) ?></td>
        <?php else: ?>
            <td class="text-danger"><?= Yii::t('user', 'Unconfirmed') ?></td>
        <?php endif ?>
    </tr>
    <tr>
        <td><strong><?= Yii::t('user', 'Block status') ?>:</strong></td>
        <?php if ($user->isBlocked): ?>
            <td class="text-danger"><?= Yii::t('user', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [$user->blocked_at]) ?></td>
        <?php else: ?>
            <td class="text-success"><?= Yii::t('user', 'Not blocked') ?></td>
        <?php endif ?>
    </tr>

    <tr>
        <?php
        $l = Html::tag('strong', Yii::t('app', 'Balance'));
        echo Html::tag('td', $l . ':');
        echo Html::tag('td', $user->balance);
        ?>

    </tr>
    <tr>
        <?php
        $l = Html::tag('strong', Yii::t('app', 'Active tariff'));
        echo Html::tag('td', $l . ':');
        /** @var \app\models\UserTariff $tariff */
        $tariff = $user->getUserTariffs()->andWhere(['=', 'status', UserTariff::STATUS_ACTIVE])->one();
        if ($tariff) {
            $txt = $tariff->title;
        } else {
            $txt = Html::tag('span', Yii::t('app', 'No'), ['class' => 'text text-warning']);
        }
        echo Html::tag('td', $txt);
        ?>
    </tr>

</table>

<?php

echo Html::a(Yii::t('app', 'Websites'), ['/client-site/index', 'ClientSiteSearch' => ['user_id' => $user->id]],
    ['target' => '_blank', 'class' => 'btn btn-default']);
echo ' ';
echo Html::a(Yii::t('app', 'Queries'), ['/client-query/index', 'ClientQuerySearch' => ['user_id' => $user->id]],
    ['target' => '_blank', 'class' => 'btn btn-default']);
echo ' ';
echo Html::a(Yii::t('app', 'Payments'), ['/payment/index', 'PaymentSearch' => ['user_id' => $user->id]],
    ['target' => '_blank', 'class' => 'btn btn-default']);
echo ' ';
echo Html::a(Yii::t('app', 'Transactions'), ['/transaction/index', 'TransactionSearch' => ['user_id' => $user->id]],
    ['target' => '_blank', 'class' => 'btn btn-default']);

?>

<?php $this->endContent() ?>
