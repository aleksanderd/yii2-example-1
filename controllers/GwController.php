<?php

namespace app\controllers;

use app\helpers\DataHelper;
use app\helpers\VoxImplant;
use app\models\BlackCallInfo;
use app\models\ClientQuery;
use app\models\ClientQueryCall;
use app\models\ClientRule;
use app\models\ClientSite;
use app\models\ClientVisit;
use app\models\Conversion;
use app\models\ModalTextStat;
use app\models\Notification;
use app\models\Transaction;
use app\models\User;
use app\models\Variable;
use app\models\variable\WOptions;
use app\models\variable\WTextsEn;
use app\models\variable\WTextsRu;
use app\models\WidgetHit;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class GwController extends Controller
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
            ],
            'verbs'      => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    '*' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    protected function getOptions($params, $saveHit = true)
    {
        $time = time();
        $url = ArrayHelper::getValue($params, 'url', '');
        $key = [
            'user_id' => ArrayHelper::getValue($params, 'user_id'),
            'site_id' => ArrayHelper::getValue($params, 'site_id'),
            'page_id' => ArrayHelper::getValue($params, 'page_id'),
        ];
        $query = new ClientQuery([
            'user_id' => $key['user_id'],
            'site_id' => $key['site_id'],
            'page_id' => $key['page_id'],
            'url' => $url,
            'at' => $time,
            'call_info' => '-options',
        ]);
        $rule = $query->findRule();

        $wOptions = (new WOptions($key))->getValues();
        //$wSettings = (new WSettings($key))->getValues();
        if ($wOptions['language'] == 'ru') {
            $wTexts = new WTextsRu($key);
        } else {
            $wTexts = new WTextsEn($key);
        }
        $wTexts = $wTexts->getValues();
        $result = [
            'options' => $wOptions,
            //'settings' => $wSettings,
            'texts' => $wTexts,
            'rule' => $rule,
        ];

        if (!$saveHit) {
            return $result;
        }
        $userAgent = ArrayHelper::getValue($params, 'user_agent', '');
        if ($isBot = DataHelper::isBotVisit($userAgent)) {
            return $result;
        }

        $referrer = ArrayHelper::getValue($params, 'referrer', '');
        $internal = DataHelper::getDomain($referrer) === DataHelper::getDomain($url);

        $ip = ArrayHelper::getValue($params, 'ip', '');
        $visit_id = ArrayHelper::getValue($params, 'visit_id', null);
        if (isset($visit_id) && $visit_id > 0) {
            if ($visit = ClientVisit::findOne($visit_id)) {
                /** @var ClientVisit $visit */
                if (!$internal && $referrer == $visit->ref_url && ($time - $visit->at) < 3600) {
                    // не делаем новые визиты если проло мало времени со старого
                    // и реф пустой (не явно что внешний - считаем что внутренний)
                    // напри. обновление страницы
                    $internal = true;
                }
            }
        }
        $wAt = ArrayHelper::getValue($params, 'at', $time * 1000) / 1000;
        $wVisitAt = ArrayHelper::getValue($params, 'status.visit_at', 0) / 1000;
        if (!(isset($visit) && $internal) || ($wAt - $wVisitAt) > 86400) {
            $newVisit = true;
            $visit = new ClientVisit([
                'previous_id' => isset($visit) ? $visit->id : null,
                'user_id' => $key['user_id'],
                'site_id' => $key['site_id'],
                'at' => $time,
                'ip' => $ip,
                'ref_url' => $referrer,
                'user_agent' => $userAgent,
            ]);
            $visit->save();
        }
        $result['visit_id'] = $visit->id;
        $hit = new WidgetHit([
            'visit_id' => $visit->id,
            'page_id' => $key['page_id'],
            'at' => $time,
            'ip' => $ip,
            'ref_url' => $referrer,
            'url' => $url,
        ]);
        // TODO поставить лог, а то есть визиты без хитов :о
        $hit->save();
        $result['hit_id'] = $hit->id;
        $vVisits = isset($newVisit) && $newVisit ? 1 : 0;
        $vUnique = ($vVisits > 0 && $visit->previous_id === null) ? 1 : 0;
        Conversion::addValues([
            'user_id' => $key['user_id'],
            'site_id' => $key['site_id'],
            'period' => DataHelper::PERIOD_HOUR,
            'datetime' => $time,
            'hits' => 1,
            'visits' => $vVisits,
            'visits_unique' => $vUnique,
        ]);
        return $result;
    }

    protected function callMe($params)
    {
        $dir = Yii::getAlias('@runtime/calls');
        FileHelper::createDirectory($dir);
        $filename = date('Y-m-d H:i:s') . '.log';
        $f = fopen($dir . '/' . $filename, 'a');
        $lParams = $params;
        unset($lParams['user']);
        unset($lParams['site']);
        fwrite($f, print_r($lParams, true));

        if (!($call_info = ArrayHelper::getValue($params, 'call_info'))) {
            fwrite($f, 'Call info is required' . PHP_EOL);
            fclose($f);
            return [
                'status' => 'error',
                'message' => 'Call info is required',
            ];
        }
        $black = BlackCallInfo::find()->where(['AND',
            ['call_info' => $call_info],
            ['OR',
                ['user_id' => $params['user_id']],
                ['user_id' => null],
            ],
        ])->exists();
        if ($black) {
            fwrite($f, 'Call info is blocked' . PHP_EOL);
            fclose($f);
            return [
                'status' => 'error',
                'message' => 'Call info is blocked',
            ];
        }
        $result  = [];
        $visit_id = ArrayHelper::getValue($params, 'visit_id');
        $visit = ClientVisit::findOne($visit_id);
        $hit_id = ArrayHelper::getValue($params, 'hit_id');
        /** @var WidgetHit $hit */
        $hit = WidgetHit::findOne($hit_id);
        if (!($visit && $hit && $hit->visit_id == $visit_id)) {
            fwrite($f, 'NULLing visit_id && hit_id'  . PHP_EOL);
            if ($visit) {
                fwrite($f, 'visit->id = ' . $visit->id . PHP_EOL);
            } else {
                fwrite($f, 'visit is NOT set'  . PHP_EOL);
            }
            if ($hit) {
                fwrite($f, 'hit->id = ' . $hit->id . PHP_EOL);
            } else {
                fwrite($f, 'hit is NOT set'  . PHP_EOL);
            }
            $visit_id = null;
            $hit_id = null;
        }

        $wAt = ArrayHelper::getValue($params, 'at', time() * 1000) / 1000;
        $wVisitAt = ArrayHelper::getValue($params, 'status.visit_at', 0) / 1000;
        $wHitAt = ArrayHelper::getValue($params, 'status.started', 0) / 1000;
        $wVisitTime = intval($wAt - $wVisitAt);
        $wHitTime = intval($wAt - $wHitAt);
        $wModalLastTrigger = ArrayHelper::getValue($params, 'status.modalLastTrigger', 'manual');
        $triggerId = DataHelper::triggerId($wModalLastTrigger);

        $query = new ClientQuery([
            'user_id' => $params['user_id'],
            'site_id' => $params['site_id'],
            'page_id' => $params['page_id'],
            'url' => $params['url'],
            'at' => time(),
            'call_info' => $call_info,
            'hit_id' => $hit_id,
            'visit_id' => $visit_id,
            'hit_time' => $wHitTime > 0 ? $wHitTime : 0,
            'visit_time' => $wVisitTime > 0 ? $wVisitTime : 0,
            'trigger' => $triggerId,
        ]);

        if ($paid = $query->checkPaid()) {
            if (!$query->save()) {
                $result['status'] = 'query-unsaved';
                $result['message'] = $query->errors;
                return $result;
            }
            if ($rule = $query->findRule()) {
                $query->status = ClientQuery::STATUS_RULE_FOUND;
                if ($query->process()) {
                    $result['status']  = 'ok';
                    $result['message'] = 'Call process started successfully .';
                } else {
                    $query->status = ClientQuery::STATUS_CALLS_INIT_FAILED;
                    $result['status']  = 'call-failed';
                    $result['message'] = 'Call process start failed.';
                }
            } else {
                $query->status = ClientQuery::STATUS_RULE_NOT_FOUND;
                $result['status']  = 'no-rule';
                $result['message'] = 'No any rules matched.';
                $result['workTime']  = ClientRule::workTime($params['user_id'], $params['site_id'], $params['page_id']);
            }
        } else {
            $query->status = ClientQuery::STATUS_UNPAID;
            $result['status']  = 'call-unpaid';
            $result['message'] = 'Call process start failed.';
            Notification::onQuery($query);
        }

        $query->updateTime()->save();

        $uni = $query->call_info_count > 1 ? 0 : 1;
        $config = [
            'user_id' => $query->user_id,
            'site_id' => $query->site_id,
            'period' => DataHelper::PERIOD_HOUR,
            'datetime' => $query->at,
            'queries' => 1,
            'queries_unique' => $uni,
            'queries_unpaid' => $paid ? 0 : 1,
        ];
        if ($paid) {
            $trName = $wModalLastTrigger . '_queries';
            if ($wModalLastTrigger != 'manual') {
                $trName = 'tr_' . $trName;
                $config['tr_total_queries'] = 1;
            }
            $config[$trName] = 1;
        }
        Conversion::addValues($config);

        $text_id = ArrayHelper::getValue($params, 'status.text_id', 0);
        if ($text_id > 0) {
            ModalTextStat::addValues([
                'text_id' => $text_id,
                'user_id' => $query->user_id,
                'site_id' => $query->site_id,
                'trigger' => $triggerId,
                'period' => DataHelper::PERIOD_HOUR,
                'datetime' => $query->at,
                'queries' => 1,
                'queries_uni' => $uni,
            ]);
        }

        $result['query'] = [
            'id' => $query->id,
            'status' => $query->status,
            'data' => $query->customData,
        ];

        fwrite($f, print_r($result, true));
        fclose($f);

        return $result;
    }

    public function deferCall($params)
    {
        $hours = ArrayHelper::getValue($params, 'hours', '');
        if (strlen($hours) < 1) {
            return ['fatal' => 'Hours is required'];
        }
        if (!($query_id = ArrayHelper::getValue($params, 'query_id'))) {
            return ['fatal' => 'Query ID is required'];
        }
        /** @var \app\models\ClientQuery $query */
        if (!($query = ClientQuery::findOne($query_id))) {
            return ['fatal' => 'Query not found'];
        }
        $query->status = ClientQuery::STATUS_DEFERRED;
        $hours = explode(',', $hours);
        sort($hours);
        $query->result['dHours'] = implode(',', $hours);
        $query->result['dTZ'] = ArrayHelper::getValue($params, 'tz', '');
        $query->save(false);
        return ['status' => 'ok'];
    }

    public function logStats($params)
    {
        $cmd = ArrayHelper::getValue($params, 'cmd');
        if (!in_array($cmd, ['showModal'])) {
            return ['status' => 'wrong cmd for stats'];
        }
        $lastName = '_wins';
        $cfg = $config = [
            'user_id' => $params['user_id'],
            'site_id' => $params['site_id'],
            'period' => DataHelper::PERIOD_HOUR,
            'datetime' => time(),
        ];
        $trigger = ArrayHelper::getValue($params, 'status.modalLastTrigger', 'manual');
        $trName = $trigger . $lastName;
        if ($trigger != 'manual') {
            $trName = 'tr_' . $trName;
            $config['tr_total' . $lastName] = 1;
        }
        $config[$trName] = 1;
        Conversion::addValues($config);

        $text_id = ArrayHelper::getValue($params, 'status.text_id', 0);
        if ($text_id > 0) {
            $config = $cfg;
            $cnt = ArrayHelper::getValue($params, 'status.text' . $text_id . '_cnt', 0);
            $config['text_id'] = $text_id;
            $config['trigger'] = DataHelper::triggerId($trigger);
            $config['wins'] = 1;
            $config['wins_uni'] = $cnt > 1 ? 0 : 1;
            ModalTextStat::addValues($config);
        }

        return ['status' => 'ok'];
    }

    public function actionCbWidget()
    {
        $post = Yii::$app->request->post();
        if (!($user_id = ArrayHelper::getValue($post, 'user_id'))) {
            return ['fatal' => 'User ID is required'];
        }
        /** @var \app\models\User|null $user */
        if (!($user = User::findOne($user_id))) {
            return ['fatal' => 'User not found'];
        }
        if ($user->isBlocked) {
            return ['fatal' => 'User is blocked'];
        }
        if (!($site_id = ArrayHelper::getValue($post, 'site_id'))) {
            return ['fatal' => 'Site ID is required'];
        }
        /** @var \app\models\ClientSite $site */
        if (!($site = ClientSite::findOne($site_id))) {
            return ['fatal' => 'Site not found'];
        }
        if ($site->user_id != $user_id) {
            return ['fatal' => 'Requested user is not owner of requested site'];
        }
        if (isset($post['url']) && strlen($post['url']) > 0) {
            $domain = DataHelper::getDomain(DataHelper::normalizeUrl($post['url']));
            if ($domain != $site->domain) {
                return ['fatal' => 'Wrong site'];
            }
        }
        $post['user'] = $user;
        $post['site'] = $site;
        $post['url'] = DataHelper::normalizeUrl(ArrayHelper::getValue($post, 'url', ''));
        $post['page_id'] = strlen($post['url']) > 0 && ($page = $site->findPageByUrl($post['url'])) ? $page->id : null;
        $post['ip'] = Yii::$app->request->userIP;
        $post['user_agent'] = Yii::$app->request->userAgent;

        $cmd  = ArrayHelper::getValue($post, 'cmd', '');

        switch ($cmd) {
            case 'options':
                return $this->getOptions($post);
                break;
            case 'defer':
                return $this->deferCall($post);
                break;
            case 'showModal':
                return $this->logStats($post);
                break;
            case 'call-me':
            default:
                return $this->callMe($post);
                break;
        }

    }

    public function actionViFinishQuery()
    {
        $post = Yii::$app->request->post();
        if (isset($post['query_id'])) {
            $query = $this->findQuery($post['query_id']);
        } else {
            return false;
        }
        $dir = Yii::getAlias('@runtime/vi');
        FileHelper::createDirectory($dir);
        $filename = sprintf('fin-%04d.log', $query->id);
        $f = fopen($dir . '/' . $filename, 'a');
        fwrite($f, "POST:\n" . print_r($post, true));
        fwrite($f, "Query customData:\n" . print_r($query->customData, true));

        try {

            if (!($mUrl = ArrayHelper::remove($query->customData, 'media_session_access_url'))) {
                throw new ForbiddenHttpException('Access url undefined.');
            }

            $vi = new VoxImplant([
                'media_session_access_url' => $mUrl,
            ]);

            $dbg = false;
            $res = $vi->sessionExec([
                'cmd' => 'get-query-result',
            ], $dbg);
            fwrite($f, "get-query-result:\n" . print_r($res, true));
            if ($status = ArrayHelper::remove($res, 'status')) {
                $query->status = $status;
            }

            $disconnected_at = false;
            $clientCost = 0;
            $cost = 0;
            // статсы звонков менеджерам
            $mCallConfigs = [];
            foreach (ArrayHelper::remove($res, 'lines', []) as $lres) {
                if (!isset($lres['stats'])) {
                    continue;
                }
                $cfg = [
                    'query_id' => $query->id,
                    'line_id' => $lres['id'],
                    'info' => $lres['info']
                ];
                foreach ($lres['stats'] as $sn => $sv) {
                    $cfg[$sn] = round(floatval($sv));
                }
                foreach (['duration', 'cost', 'direction'] as $sp) {
                    if ($sv = ArrayHelper::getValue($lres, 'e.' . $sp)) {
                        $cfg[$sp] = $sv;
                    }
                }
                if (!($dAt = ArrayHelper::getValue($cfg, 'disconnected_at')) || $dAt < $disconnected_at) {
                    $disconnected_at = $dAt;
                }
                $mCallConfigs[] = $cfg;
            }
            // статсы звонка заказчику
            $ce = ArrayHelper::remove($res, 'ce', []);
            if ($query->status > 999 && ($cStats = ArrayHelper::remove($res, 'cStats'))) {
                $cCallConfig = [
                    'query_id' => $query->id,
                    'info' => $query->call_info,
                ];
                foreach ($cStats as $sn => $sv) {
                    $cCallConfig[$sn] = round(floatval($sv));
                }
                foreach (['duration', 'cost', 'direction'] as $sp) {
                    if (isset($ce[$sp])) {
                        $cCallConfig[$sp] = $ce[$sp];
                    }
                }
                if (!($dAt = ArrayHelper::getValue($cCallConfig, 'disconnected_at')) || $dAt < $disconnected_at) {
                    $disconnected_at = $dAt;
                }
            } else {
                $cCallConfig = false;
            }

            $duration = false;
            foreach (array_merge($mCallConfigs, [$cCallConfig]) as $cfg) {
                if (!is_array($cfg)) {
                    continue;
                }
                $call = new ClientQueryCall($cfg);
                if (isset($call->connected_at)) {
                    if (!isset($call->disconnected_at) || $call->disconnected_at < $call->connected_at) {
                        fwrite($f, "!!! disconnected_at is NOT set, config: \n" . print_r($cfg, true));
                        if (isset($call->duration)) {
                            $call->disconnected_at = $call->started_at + $call->duration;
                            fwrite($f, "!!! set disconnected_at = started_at + duration\n");
                        } else {
                            fwrite($f, "!!! set disconnected_at = safe value\n");
                            $call->disconnected_at = $disconnected_at;
                            $call->duration = null;
                        }
                    }
                    if (!isset($call->duration)) {
                        fwrite($f, "!!! duration is NOT set\n");
                        $call->duration = $call->disconnected_at - $call->connected_at;
                        if ($call->duration < 0) {
                            fwrite($f, "!!! calculated duration < 0, fixing\n");
                            $call->duration = 0;
                            $call->disconnected_at = $call->connected_at;
                        }
                    }
                    if (!$duration || $call->duration < $duration) {
                        $duration = $call->duration;
                    }
                }
                $call->updateClientCost();
                $clientCost += $call->client_cost;
                $cost += $call->cost;
                if (!$call->save()) {
                    fwrite($f, "!!! ClientQueryCall save failed:\n" . print_r($cfg, true));
                    fwrite($f, print_r($call->errors, true));
                }
            }

            if ($recordUrl = ArrayHelper::remove($res, 'recordUrl')) {
                /*копируем на S3*/
                $storage = \Yii::$app->storage;
                if ($p = strpos($recordUrl, '?')) {
                    $recordUrl = substr($recordUrl, 0, $p);
                }
                $url = $storage->save($recordUrl, 'records/' . $query->user_id . '/' . basename($recordUrl));
                if (isset($url)) {
                    $query->record['url'] = $url;
                } else {
                    $query->record['url'] = $recordUrl;
                }
                $query->record_time = ArrayHelper::getValue($ce, 'duration', $duration);
            }

            $query->cost = $cost;
            if ($tariff = $query->userTariff) {
                $query->client_cost = 0;
                $tariff->queries_used++;
                $tariff->seconds_used += $query->record_time;
                $tariff->save(false, ['queries_used', 'seconds_used']);
            } else {
                $query->client_cost = $clientCost;
                if ($clientCost > 0) {
                    $transaction = new Transaction([
                        'user_id' => $query->user_id,
                        'query_id' => $query->id,
                        'amount' => -1 * $clientCost,
                    ]);
                    if (!$transaction->save()) {
                        fwrite($f, "!!! Transaction save failed:\n");
                        fwrite($f, print_r($transaction->errors, true));
                    }
                }
            }

            $query->result = $res;

            $res = $vi->sessionExec(['cmd' => 'terminate']);
            fwrite($f, "terminate:\n" . print_r($res, true));

            $query->updateTime()->save();
            Conversion::addValues([
                'user_id' => $query->user_id,
                'site_id' => $query->site_id,
                'period' => DataHelper::PERIOD_HOUR,
                'datetime' => $query->at,
                'queries_success' => $status >= ClientQuery::STATUS_COMM_SUCCESS,
                'queries_calls' => $query->getCalls()->count(),
                'record_time' => $query->record_time,
                'client_cost' => $query->client_cost,
                'cost' => $query->getCalls()->sum('cost'),
            ]);
            Notification::onQuery($query);


        } catch (\Exception $e) {
            fwrite($f, 'Exception!' . $e->getMessage() . "\n");
            fclose($f);
            throw $e;
        }
        fclose($f);
        exit;
    }

    /**
     * @param $id
     * @param array $options
     * @return ClientQuery
     * @throws NotFoundHttpException
     */
    protected function findQuery($id, $options = [])
    {
        if (($model = ClientQuery::findOne($id)) !== null) {
            return Yii::configure($model, $options);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested query #{id} does not exist.', ['id' => $id]));
        }
    }

}
