<?php

namespace app\base;

use app\helpers\DataHelper;
use app\models\ReferralStats;
use app\models\ReferralUrl;
use app\models\User;
use app\models\Variable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Cookie;

/**
 * Class Application
 *
 * @property-read string $currencyCode Код валюты приложения, например USD или RUB
 */
class Application extends \yii\web\Application
{

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->initLocale();
    }

    public function initLocale()
    {
        $language = 'auto';
        if (!$this->user->isGuest) {
            if ($t = Variable::sGet('u.settings.language', $this->user->id)) {
                $language = $t;
            }
            if ($t = Variable::sGet('u.settings.timezone', $this->user->id)) {
                $this->timeZone = $t;
            }
        }
        if ($language == 'auto') {
            $allowed = ['en', 'ru'];
            $accept = DataHelper::parseAcceptLanguages(ArrayHelper::getValue($_SERVER, 'HTTP_ACCEPT_LANGUAGE', ''));
            foreach ($accept as $l => $q) {
                $al = substr($l, 0, 2);
                if (in_array($al, $allowed)) {
                    $this->language = $al;
                    break;
                }
            }
        } else {
            $this->language = $language;
        }
        $this->formatter->currencyCode = $this->getCurrencyCode();
    }

    public function init()
    {
        parent::init();
        if ($this->user->isGuest && isset(Yii::$app->request)) {
            $time = time();
            // Для гостей собираем и прибираем инфу о переходе, рефе и тд.
            // TODO Добавить еще проверку на экшены из GW контроллера, для них ничего этого тоже не надо.
            $expire = time() + 33*86400;
            if ($r = ArrayHelper::getValue($this->request->queryParams, 'r')) {
                $this->response->cookies->add(new Cookie([
                    'expire' => $expire,
                    'name' => 'r',
                    'value' => $r,
                ]));
            }
            $http_referrer = ArrayHelper::getValue($this->request->queryParams, 'http_referrer',
                $this->request->cookies->getValue('http_referrer', Yii::$app->request->referrer));
            if (DataHelper::getDomain($http_referrer) != DataHelper::getDomain(Url::to('/', true))) {
                if (isset($http_referrer) && strlen($http_referrer) > 7) {
                    $this->response->cookies->add(new Cookie([
                        'name' => 'http_referrer',
                        'value' => $http_referrer,
                    ]));
                }
                if (isset($r)) {
                    // Записываем статсы по реф.урлам
                    $ids = explode(ReferralUrl::ID_DELIMITER, $r);
                    if ($partner = User::findOne($ids[0])) {
                        if (count($ids) > 1) {
                            $url = ReferralUrl::findOne([
                                'user_id' => $partner->id,
                                'id' => $ids[1],
                            ]);
                        }
                        if (!isset($url)) {
                            $url = ReferralUrl::defaultReferralUrl($partner->id);
                        }
                        $lastVisit = $this->request->cookies->getValue('last_visit', 0);
                        $lastHit = $this->request->cookies->getValue('last_hit', 0);
                        ReferralStats::addValues([
                            'user_id' => $partner->id,
                            'url_id' => $url->id,
                            'datetime' => $time,
                            'visits' => $lastHit > 0 ? 0 : 1,
                            'visits_unique' => $lastVisit > 0 ? 0 : 1,
                        ]);
                    }
                }
                $this->response->cookies->add(new Cookie([
                    'expire' => $expire,
                    'name' => 'last_visit',
                    'value' => $time,
                ]));
                $this->response->cookies->add(new Cookie([
                    'name' => 'last_hit',
                    'value' => $time,
                ]));
            }
        }
// Для дебага:
//        $r = ArrayHelper::getValue(Yii::$app, 'request.queryParams.r',
//            Yii::$app->request->cookies->getValue('r'));
//        $http_referrer = ArrayHelper::getValue(Yii::$app, 'request.queryParams.http_referrer',
//            Yii::$app->request->cookies->getValue('http_referrer'));
    }

    public function getCurrencyCode()
    {
        return ArrayHelper::getValue($this->params, 'currencyCode', 'RUB');
    }

}
