<?php

namespace app\helpers;

use Yii;
use app\models\User;
use app\models\Payout;
use yii\helpers\ArrayHelper;

class UniHelper extends \flyiing\helpers\UniHelper {

    public static function getUserActions(User $model, $actions = null)
    {
        $defaults = [
            'block' => [
                'icon' => 'payout-retry',
                'label' => Yii::t('app', 'Retry'),
                'url' => ['retry', 'id' => $model->id],
                'options' => [
                    'class' => 'btn-primary',
                ],
            ],
        ];
        return static::getModelActions($model, $actions);
    }

    public static function getPayoutActions(Payout $model, $actions = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if ($actions === null) {
            $actions = [
                'view',
                'update',
                'retry',
                'delete',
            ];
        }
        $defaults = [
            'retry' => [
                'icon' => 'payout-retry',
                'label' => Yii::t('app', 'Retry'),
                'url' => ['retry', 'id' => $model->id],
                'options' => [
                    'class' => 'btn-primary',
                ],
            ],
        ];
        foreach ($defaults as $k => $v) {
            if (isset($actions[$k]) && is_array($actions[$k])) {
                $actions[$k] = ArrayHelper::merge($v, $actions[$k]);
            } else if (($ak = array_search($k, $actions)) !== false) {
                $offset = array_search($ak, array_keys($actions));
                $p1 = array_slice($actions, 0, $offset, true);
                $p2 = array_slice($actions, $offset+1, null, true);
                $actions = array_merge($p1, [$k => $v], $p2);
            }
        }
        $del = [];
        if (!(in_array($model->status, [Payout::STATUS_COMPLETE, Payout::STATUS_REJECTED]) && $user->isPayoutAllowed)) {
            $del[] = 'retry';
        }
        if (!$model->isWritable) {
            $del[] = 'update';
            if ($model->status != Payout::STATUS_REJECTED) {
                $del[] = 'delete';
            }
        }
        foreach ($actions as $k => $v) {
            $key = is_string($v) ? $v : $k;
            if (in_array($key, $del)) {
                unset($actions[$k]);
            }
        }
        return static::getModelActions($model, $actions);
   }

    public static function getPayoutAdminActions(Payout $model)
    {
        $result = [];
        if ($model->status === Payout::STATUS_REQUEST) {
            $result[] = [
                'icon' => 'payout-start',
                'label' => Yii::t('app', 'Start payout'),
                'url' => ['/payout/status', 'id' => $model->id, 'status' => Payout::STATUS_IN_PROCESS],
                'options' => [
                    'class' => 'btn btn-primary',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to start this payout?'),
                        'method' => 'post',
                    ],
                ],
            ];
        } else if ($model->status === Payout::STATUS_IN_PROCESS) {
            $result[] = [
                'icon' => 'payout-complete',
                'label' => Yii::t('app', 'Complete payout'),
                'url' => ['/payout/status', 'id' => $model->id, 'status' => Payout::STATUS_COMPLETE],
                'options' => [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to complete this payout?'),
                        'method' => 'post',
                    ],
                ],
            ];
        } else {
            return [];
        }
        $result[] = [
            'icon' => 'payout-reject',
            'label' => Yii::t('app', 'Reject payout'),
            'url' => ['/payout/status', 'id' => $model->id, 'status' => Payout::STATUS_REJECTED],
            'options' => [
                'class' => 'btn btn-warning',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to reject this payout?'),
                    'method' => 'post',
                ],
            ],
        ];
        return $result;
    }
}
