<?php

namespace app\helpers;

use app\models\ClientSite;
use app\models\Payment;
use app\models\Payout;
use app\models\STicket;
use app\models\User;
use app\models\UserReferral;
use app\models\UserTariff;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Url;
use flyiing\helpers\Html;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class ViewHelper extends Object {

    public static function sitesButton(User $user)
    {
        $linkWarnBtn = $linkBtn = [
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn btn-success',
            ],
        ];
        $linkWarnBtn['options']['class'] .= ' btn-warning-dyn';
        $sitesCount = $user->getClientSites()->count();
        return Html::buttonGroup([
            'buttons' => [
                '__default__' => $sitesCount > 0 ? $linkBtn : $linkWarnBtn,
                'sites' => [
                    'label' => Yii::t('app', 'Websites') .' '. Html::tag('span', $sitesCount, ['class' => 'badge']),
                    'icon' => 'client-site',
                    'url' => ['/client-site/index'],
                ],
                'add-site' => [
                    'label' => Html::icon('plus', '{i}'),
                    'url' => ['/client-site/create'],
                ],
            ],
            'options' => ['class' => 'btn-group btn-group-xs'],
        ]);
    }

    public static function pagesButton(User $user, ClientSite $site = null)
    {
        $linkBtn = [
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn btn-success',
            ],
        ];
        $sitesCount = $user->getClientSites()->count();
        $options = $sitesCount > 0 ? [] : ['disabled' => 'disabled'];
        $pages = $user->getClientPages();
        if ($site !== null) {
            $pages->andWhere(['site_id' => $site->id]);
        }
        $pages = $pages->all();
        return Html::buttonGroup([
            'buttons' => [
                '__default__' => $linkBtn,
                'pages' => [
                    'label' => Yii::t('app', 'Pages') .' '. Html::tag('span', count($pages), ['class' => 'badge']),
                    'icon' => 'client-page',
                    'url' => ['/client-page/index', 'ClientPageSearch' => $site ? ['site_id' => $site->id] : []],
                    'options' => $options,
                ],
                'add-page' => [
                    'label' => Html::icon('plus', '{i}'),
                    'url' => ['/client-page/create', 'site_id' => $site ? $site->id : null],
                    'options' => $options,
                ],
            ],
            'options' => ['class' => 'btn-group btn-group-xs'],
        ]);
    }

    public static function linesButton(User $user)
    {
        $linkWarnBtn = $linkBtn = [
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn btn-success',
            ],
        ];
        $linkWarnBtn['options']['class'] .= ' btn-warning-dyn';
        $linesCount = $user->getClientLines()->count();
        $sitesCount = $user->getClientSites()->count();
        $options = $sitesCount > 0 ? [] : ['disabled' => 'disabled'];
        return Html::buttonGroup([
            'buttons' => [
                '__default__' => $linesCount > 0 || $sitesCount < 1 ? $linkBtn : $linkWarnBtn,
                'lines' => [
                    'label' => Yii::t('app', 'Phone lines') .' '. Html::tag('span', $linesCount, ['class' => 'badge']),
                    'icon' => 'client-line',
                    'url' => ['/client-line/index'],
                    'options' => $options,
                ],
                'add-line' => [
                    'label' => Html::icon('plus', '{i}'),
                    'url' => ['/client-line/create'],
                    'options' => $options,
                ],
            ],
            'options' => ['class' => 'btn-group btn-group-xs'],
        ]);
    }

    public static function rulesButton(User $user, ClientSite $site = null)
    {
        $linkWarnBtn = $linkBtn = [
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn btn-success',
            ],
        ];
        $linkWarnBtn['options']['class'] .= ' btn-warning-dyn';
        $linesCount = $user->getClientLines()->count();
        $sitesCount = $user->getClientSites()->count();
        $rules = $user->getClientRules();
        if ($site !== null) {
            $rules->andWhere(['OR', ['site_id' => $site->id], ['site_id' => null]]);
        }
        $rulesCount = $rules->count();
        $options = ($linesCount > 0 && $sitesCount > 0) ? [] : ['disabled' => 'disabled'];
        return Html::buttonGroup([
            'buttons' => [
                '__default__' => ($rulesCount > 0) || ($linesCount < 1) || ($sitesCount < 1) ? $linkBtn : $linkWarnBtn,
                'rules' => [
                    'label' => Yii::t('app', 'Rules') .' '. Html::tag('span', $rulesCount, ['class' => 'badge']),
                    'icon' => 'client-rule',
                    'url' => ['/client-rule/index'],
                    'options' => $options,
                ],
                'add-rule' => [
                    'label' => Html::icon('plus', '{i}'),
                    'url' => ['/client-rule/create', 'site_id' => $site ? $site->id : null],
                    'options' => $options,
                ],
            ],
            'options' => ['class' => 'btn-group btn-group-xs'],
        ]);
    }

    public static function uspButtons($config = [])
    {
        /** @var \app\models\User $user */
        $user = ArrayHelper::getValue($config, 'user', Yii::$app->user->identity);
        /** @var \app\models\ClientSite|null $site */
        $site = ArrayHelper::getValue($config, 'site');
        $result = [];
        foreach (ArrayHelper::getValue($config, 'items', []) as $name) {
            if ($name == 'sites') {
                $result['sites'] = static::sitesButton($user, $site);
            } else if ($name == 'pages') {
                $result['pages'] = static::pagesButton($user);
            } else if ($name == 'lines') {
                $result['lines'] = static::linesButton($user);
            } else if ($name == 'rules') {
                $result['rules'] = static::rulesButton($user, $site);
            }
        }
        return $result;
    }

    public static function changeText($current, $previous = 0, $options = [])
    {
        if ($previous == 0) {
            return '-';
        }
        $format = ArrayHelper::getValue($options, 'format', '%.1f%%');
        $diff = $current - $previous;
        $change = abs(100 * $diff / $previous);
        $changeText = sprintf($format, $change);
        $changeText .= $diff > 0 ? Html::icon('level-up') : Html::icon('level-down');
        return $changeText;
    }

    public static function valuesRow($values)
    {
        $count = count($values);
        $csz = round(12 / $count);
        $content = '';
        foreach ($values as $v) {
            $content .= Html::tag('div', $v, ['class' => 'col-sm-' . $csz]);
        }
        if (strlen($content) > 0) {
            $content = Html::tag('div', $content, ['class' => 'row']);
        }
        return $content;
    }

    public static function userReferralStatusSpan(UserReferral $model) {
        if ($model->status >= UserReferral::STATUS_FINISHED) {
            $class = 'success';
        } else if ($model->status >= UserReferral::STATUS_ACTIVE) {
            $class = 'primary';
        } else {
            $class = 'default';
        }
        return Html::tag('span', $model->statusText, ['class' => 'label label-'.$class]);
    }

    public static function paymentStatusSpan(Payment $model)
    {
        $tLink ='';
        if ($model->status == Payment::STATUS_ERROR) {
            $class = 'error';
            $icon = 'payment-error';
        } else if ($model->status == Payment::STATUS_CANCELED) {
            $class = 'warning';
            $icon = 'payment-canceled';
        } else if ($model->status == Payment::STATUS_COMPLETED) {
            $class = 'success';
            if (count($model->transactions) < 1) {
                $tLink = ' &gt;&gt; ' . Html::a(Yii::t('app', '+funds transaction'), ['//payment/complete', 'pid' => $model->id]);
            }
            $icon = 'payment-completed';
        } else {
            $class = 'default';
            $icon = 'question';
        }
        $l = Html::icon($icon) . ArrayHelper::getValue(Payment::statusLabels(), $model->status, $model->status);
        $c = Html::tag('span', $l, ['class' => 'label label-' . $class]);
        return  $c .' ' . $tLink;
    }

    public static function payoutStatusSpan(Payout $model)
    {
        if ($model->status == Payout::STATUS_REJECTED) {
            $class = 'warning';
            $icon = 'payout-reject';
        } else if ($model->status >= Payout::STATUS_COMPLETE) {
            $class = 'success';
            $icon = 'payout-complete';
        } else if ($model->status >= Payout::STATUS_IN_PROCESS) {
            $class = 'info';
            $icon = 'payout-process';
        } else if ($model->status >= Payout::STATUS_REQUEST) {
            $class = 'primary';
            $icon = 'payout-request';
        } else {
            $class = 'default';
            $icon = 'question';
        }
        return Html::tag('span', Html::icon($icon) . $model->statusText, ['class' => 'label label-'.$class]);
    }

    public static function userTariffStatusSpan(UserTariff $model)
    {
        $label = ArrayHelper::getValue($model->statusLabels(), $model->status, $model->status .':'. Yii::t('app', 'Unknown status'));
        if ($model->status == UserTariff::STATUS_RENEW) {
            $class = 'danger';
            $icon = 'tariff-status-renew';
        } else if ($model->status == UserTariff::STATUS_READY) {
            $class = 'primary';
            $icon = 'tariff-status-ready';
        } else if ($model->status == UserTariff::STATUS_ACTIVE) {
            $class = 'success';
            $icon = 'tariff-status-active';
        } else if ($model->status < 0) {
            $class = 'info';
            $icon = 'tariff-status-finished';
        } else {
            $class = 'default';
            $icon = 'tariff-status-draft';
        }
        return Html::tag('span', Html::icon($icon) . $label, ['class' => 'label label-' . $class]);
    }

    public static function UserReferralScheme(UserReferral $model)
    {
        if ($model->isPaid) {
            return $model->schemeText;
        }
        $schemes = $model->schemeLabelsPct();
        $buttons = [];
        foreach ($schemes as $k => $v) {
            $config = [
                'label' => $v,
                'options' => [
                    'class' => 'btn btn-xs',
                ],
            ];
            if ($k == $model->scheme) {
                $config['options']['disabled'] = 'disabled';
                Html::addCssClass($config['options'], 'btn-success');
            } else {
                Html::addCssClass($config['options'], 'btn-success  btn-outline');
                $msg = Yii::t('app', 'Are you sure you want to switch to scheme: "{scheme}" ?', ['scheme' => $v]);
                if ($model->isActive) {
                    $msg .= "\n" . Yii::t('app', 'Since the referral is active, you will be paid right after the confirm.');
                }
                $config = ArrayHelper::merge($config, [
                    'tagName' => 'a',
                    'options' => [
                        'href' => Url::to(['/user-referral/update-scheme',
                            'partner_id' => $model->partner_id,
                            'user_id' => $model->user_id,
                            'scheme_id' => $k,
                        ]),
                        'data-confirm' => $msg,
                        'data-method' => 'post',
                    ],
                ]);
            }
            $buttons[] = $config;
        }
        return ButtonGroup::widget([
            'buttons' => $buttons,
        ]);
    }

    public static function payoutAdminActions(Payout $model)
    {
        $config = [
            'tagName' => 'a',
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn',
            ],
        ];
        $buttons = [];
        if ($model->status == Payout::STATUS_REQUEST) {
            $buttons[] = ArrayHelper::merge($config, [
                'label' => Yii::t('app', 'Start payout process'),
                'options' => [
                    'href' => Url::to(['/payout/start', 'id' => $model->id]),
                    'class' => 'btn btn-success',
                ],
            ]);
        }
        $buttons[] = ArrayHelper::merge($config, [
            'label' => Yii::t('app', 'Reject payout'),
            'options' => [
                'href' => Url::to(['/payout/reject', 'id' => $model->id]),
                'class' => 'btn btn-danger',
            ],
        ]);
        return ButtonGroup::widget([
            'buttons' => $buttons,
            'options' => [
                'class' => 'btn-group btn-group-xs btn-group-vertical',
            ],
        ]);
    }

    public static function getIOAnimations()
    {
        return [
            'bounce{io}' => Yii::t('app', 'Bounce'),
            'fade{io}' => Yii::t('app', 'Fade'),
            'flip{io}X' => Yii::t('app', 'Flip X'),
            'flip{io}Y' => Yii::t('app', 'Flip Y'),
            //'lightSpeed{io}' => Yii::t('app', 'Light speed'),
            'rotate{io}' => Yii::t('app', 'Rotate'),
            'zoom{io}' => Yii::t('app', 'Zoom'),
            'bounce{io}{side}' => Yii::t('app', 'Bounce side'),
            'fade{io}{side}' => Yii::t('app', 'Fade side'),
            'fade{io}{side}Big' => Yii::t('app', 'Fade side BIG'),
            //'rotate{io}{side}' => Yii::t('app', 'Rotate side'),
            'slide{io}{side}' => Yii::t('app', 'Slide side'),
            'zoom{io}{side}' => Yii::t('app', 'Zoom side'),
        ];
    }

    public static function wCodeResult(ClientSite $model)
    {
        if ($model->w_check_result == ClientSite::CODE_ERROR) {
            $class = 'danger';
        } else if ($model->w_check_result == ClientSite::CODE_NONE) {
            $class = 'warning';
        } else if ($model->w_check_result == ClientSite::CODE_OK) {
            $class = 'success';
        } else {
            $class = 'default';
        }
        $text = ArrayHelper::getValue($model->wCodeResultStatuses(), $model->w_check_result, Yii::t('app', 'Unknown'));
        return Html::tag('span', $text, ['class' => 'label label-' . $class]);
    }

    public static function markedInteger($value)
    {
        if ($value == 0) {
            return Html::tag('small', $value, ['class' => 'text-muted']);
        } else {
            return Html::tag('strong', $value);
        }
    }

    public static function ticketStatusSpan(STicket $model)
    {
        $status = $model->status;
        if ($status == STicket::STATUS_NEW) {
            $class = 'warning';
        } else if ($status == STicket::STATUS_OPEN) {
            $class = 'info';
        } else if ($status == STicket::STATUS_REPLIED) {
            $class = 'success';
        } else {
            $class = 'default';
        }

        $txt = ArrayHelper::getValue(STicket::statusLabels(), $status, Yii::t('app', 'Unknown status'));
        return Html::tag('span', $txt, ['class' => 'label label-' . $class]);
    }

}
