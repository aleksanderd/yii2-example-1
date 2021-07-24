<?php

namespace app\helpers;

use app\models\ClientPage;
use app\models\ClientSite;
use app\models\ClientLine;
use app\models\User;
use app\models\UserTariff;
use app\models\Variable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ModelsHelper
{

    public static function getSitesSelectList($user_id = 0, $keys = false)
    {
        $find = ClientSite::find();
        if ($user_id > 0) {
            $find->andWhere(['user_id' => $user_id]);
        }
        $data = ArrayHelper::map($find->all(), 'id', function ($el) {
            /** @var $el ClientSite */
            return array_merge($el->attributes, ['text' => $el->title]);
        });
        if ($keys) {
            return $data;
        } else {
            return array_values($data);
        }
    }

    public static function getLinesSelectList($user_id = 0, $keys = false)
    {
        $find = ClientLine::find();
        if ($user_id > 0) {
            $find->andWhere(['user_id' => $user_id]);
        }
        $data = ArrayHelper::map($find->all(), 'id', function ($el) {
            /** @var $el ClientLine */
            return array_merge($el->attributes, ['text' => $el->title]);
        });
        if (sizeof($data) == 0) {
            $data = [['id' => 0, 'text' => Yii::t('app', 'No phone lines')]];
        }
        if ($keys) {
            return $data;
        } else {
            return array_values($data);
        }
    }

    public static function getPatternTypesSelectList()
    {
        $result = [];
        foreach (ClientPage::getTypeLabels() as $id => $text) {
            $result[] = compact(['id', 'text']);
        }
        return $result;
    }

    public static function getSelectData($objects, $map = ['id', 'name' => 'title'])
    {
        if (!is_array($objects)) {
            return false;
        }
        $result = [];
        foreach ($objects as $object) {
            $item = [];
            // TODO добавить проверку и выполнение callable
            foreach ($map as $dstField => $srcField) {
                if (is_integer($dstField)) {
                    $dstField = $srcField;
                }
                if (($value = ArrayHelper::getValue($object, $srcField))) {
                    $item[$dstField] = $value;
                }
            }
            if (sizeof($item) > 0) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public static function userMasterWarnings(User $user = null, ClientSite $site = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->identity;
        }
        if ($user->isAdmin) {
            return false;
        }
        $sitesCount = $user->getClientSites()->count();
        if ($sitesCount < 1) {
            return Yii::t('app', 'Warning! To start using the service, please <a href="{url}" class="btn btn-sm btn-primary btn-warning-dyn">add website</a>.', [
                'url' => Url::to('/client-site/create'),
            ]);
        } else if (($linesCount = $user->getClientLines()->count()) < 1) {
            return Yii::t('app', 'Warning! To start using the service, please <a href="{url}" class="btn btn-sm btn-primary btn-warning-dyn">add line</a>.', [
                'url' => Url::to('/client-line/create'),
            ]);
        } else {
            $rulesCount = $user->getClientRules();
            if ($site !== null) {
                $rulesCount->andWhere(['OR', ['site_id' => $site->id], ['site_id' => null]]);
            }
            $rulesCount = $rulesCount->count();
            if ($rulesCount < 1) {
                return Yii::t('app', 'Warning! To start using the service, please <a href="{url}" class="btn btn-sm btn-primary btn-warning-dyn">add rule</a>.', [
                    'url' => Url::to('/client-rule/create'),
                ]);
            }
        }

        // тарифы
        $active = UserTariff::find()->where([
            'user_id' => $user->id,
            'status' => UserTariff::STATUS_ACTIVE
        ])->exists();

        if (!$active) {
            $trials = UserTariff::find()->where([
                'user_id' => $user->id,
                'status' => UserTariff::STATUS_READY,
                'price' => 0,
            ])->exists();
            if ($trials) {
                return Yii::t('app', 'Warning! You have not any active tariff yet, but there is one free and ready to use. You can activate it in <a href="{url}" class="btn btn-sm btn-primary btn-warning-dyn">Tariffs</a> section.', [
                    'url' => Url::to('/user-tariff/index'),
                ]);
            }
        }

        return false;
    }

    public static function userLists(User $user)
    {
        $time = time();
        $timeoutNewUser = ($v = Variable::sGet('s.settings.timeoutNewUser')) ? $v * 86400 : 14 * 86400;
        $timeoutActiveUser = ($v = Variable::sGet('s.settings.timeoutActiveUser')) ? $v * 86400 : 30 * 86400;
        $timeoutInactiveUser = ($v = Variable::sGet('s.settings.timeoutInactiveUser')) ? $v * 86400 : 365 * 86400;

        $lastTransaction = $user->getTransactions()->max('at');
        $active = $lastTransaction && (($time - $lastTransaction) < $timeoutActiveUser);
        if ($active) {
            return 'active';
        } else if (($time - $user->created_at) < $timeoutNewUser) {
            return 'new';
        } else if (($time - ($lastTransaction ? $lastTransaction : $user->created_at)) < $timeoutInactiveUser) {
            return 'inactive';
        } else {
            return 'trash';
        }
    }

    /**
     * Возвращает `true` если пользователь принял партнёрское соглашение, иначе `false`.
     *
     * @param $user
     * @return bool
     */
    public static function userPartnerAgreed(User $user)
    {
        if ($user->isAdmin) {
            return true;
        }
        $agreedVersion = Variable::sGet('u.referralAgreementVersion', $user->id);
        $referralAgreementVersion = Variable::sGet('s.settings.referralAgreementVersion');
        return $agreedVersion == $referralAgreementVersion;
    }

}
