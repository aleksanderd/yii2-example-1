<?php

namespace app\commands;

use app\helpers\ModelsHelper;
use app\models\ClientQuery;
use app\models\ClientSite;
use app\models\Notification;
use app\models\Tariff;
use app\models\User;
use app\models\UserTariff;
use app\models\UserTariffSearch;
use app\models\Variable;
use app\models\VariableValue;
use Yii;
use app\helpers\DataHelper;
use app\models\ClientVisit;
use app\models\Conversion;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class ToolsController extends Controller {

    public function actionClearBotsVisits($delete = false)
    {

        /** @var \app\models\ClientVisit[] $visits */
        $visits = ClientVisit::find()->all();
        $deleted = 0;
        foreach ($visits as $v) {
            if ($bot = DataHelper::isBotVisit($v->user_agent)) {
                $this->stdout($v->user_agent . ' is bot => ' . $bot . PHP_EOL);
                if ($delete) {
                    if ($v->delete()) {
                        $deleted++;
                        $this->stdout('DELETED!' . PHP_EOL, Console::FG_RED);
                    } else {
                        print_r($v->errors);
                    }
                }
            }
        }
        $this->stdout(sprintf('Deleted %d visits', $deleted) . PHP_EOL);

    }

    public function actionRewriteConversion()
    {
        Conversion::deleteAll();

        $visits = new Query();
        $visits->select([
            'user_id' => 'v.user_id',
            'site_id' => 'v.site_id',
            'previous_id' => 'v.previous_id',
            'at' => 'v.at',
            'hits' => 'COUNT(h.id)',
        ]);
        $visits->from('{{client_visit}} v, {{widget_hit}} h')
            ->where('v.id = h.visit_id')
            ->groupBy('v.id')
            ->orderBy(['user_id' => SORT_DESC, 'site_id' => SORT_DESC, 'at' => SORT_DESC]);
        //echo $visits->createCommand()->rawSql . PHP_EOL;
        $visits = $visits->all();
        $visitsTotal = count($visits);
        $this->stdout(sprintf('Total visits: %d', $visitsTotal) . PHP_EOL);
        $v = 0;
        $pct = '';
        $data = [];
        foreach ($visits as $visit) {
            $v++;
            $nPct = sprintf('%.1f%%', 100 * $v / $visitsTotal);
            if ($nPct != $pct) {
                $pct = $nPct;
                $this->stdout("\r".$pct);
            }
            $config = [
                'user_id' => $visit['user_id'],
                'site_id' => $visit['site_id'],
                'datetime' => DataHelper::truncateDatetime($visit['at'], DataHelper::PERIOD_HOUR),
                'period' => DataHelper::PERIOD_HOUR,
                'hits' => $visit['hits'],
                'visits' => 1,
                'visits_unique' => isset($visit['previous_id']) ? 0 : 1,
            ];
            $key = sprintf('%d-%d-%d-%d',
                $config['user_id'], $config['site_id'], $config['period'], $config['datetime']);

            if (isset($data[$key])) {
                $tar = array_diff_key($config, array_flip(['user_id', 'site_id', 'period', 'datetime']));
                foreach ($tar as $name => $value) {
                    $data[$key][$name] += $value;
                }
            } else {
                $data[$key] = $config;
            }

        }
        $this->stdout(' Done' . PHP_EOL);

        /** @var \app\models\ClientQuery[] $queries */
        $queries = ClientQuery::find()->all();
        $total = count($queries);
        $this->stdout(sprintf('Total queries: %d', $total) . PHP_EOL);
        $v = 0;
        $pct = '';
        foreach ($queries as $query) {
            $v++;
            $nPct = sprintf('%.1f%%', 100 * $v / $total);
            if ($nPct != $pct) {
                $pct = $nPct;
                $this->stdout("\r".$pct);
            }
            $config = [
                'user_id' => $query->user_id,
                'site_id' => $query->site_id,
                'datetime' => DataHelper::truncateDatetime($query->at, DataHelper::PERIOD_HOUR),
                'period' => DataHelper::PERIOD_HOUR,
                'queries' => 1,
                'queries_unique' => $query->call_info_count > 1 ? 0 : 1,
                'queries_success' => $query->status < ClientQuery::STATUS_COMM_SUCCESS ? 0 : 1,
                'queries_unpaid' => $query->status == ClientQuery::STATUS_UNPAID ? 1 : 0,
                'queries_calls' => $query->getCalls()->count(),
                'record_time' => $query->record_time,
                'client_cost' => $query->client_cost,
                'cost' => $query->getCalls()->sum('cost'),
            ];
            $key = sprintf('%d-%d-%d-%d',
                $config['user_id'], $config['site_id'], $config['period'], $config['datetime']);

            if (isset($data[$key])) {
                $tar = array_diff_key($config, array_flip(['user_id', 'site_id', 'period', 'datetime']));
                foreach ($tar as $name => $value) {
                    $data[$key][$name] = ArrayHelper::getValue($data[$key], $name, 0) + $value;
                }
            } else {
                $data[$key] = $config;
            }
        }
        $this->stdout(' Done' . PHP_EOL);

        $total = count($data);
        $this->stdout(sprintf('Result records: %d', $total) . PHP_EOL);
        $v = 0;
        $pct = '';
        $q = 0;
        foreach ($data as $config) {
            $v++;
            $nPct = sprintf('%.1f%%', 100 * $v / $total);
            if ($nPct != $pct) {
                $pct = $nPct;
                $this->stdout("\r".$pct);
            }
            $q += ArrayHelper::getValue($config, 'queries', 0);
            Conversion::addValues($config);
        }
        $this->stdout(' Done' . PHP_EOL);
    }

    public function actionNormalizeUrls()
    {
        /** @var ClientQuery[] $queries */
        $queries = ClientQuery::find()->all();
        foreach ($queries as $q) {
            $url = DataHelper::normalizeUrl($q->url);
            if ($url !== $q->url) {
                $this->stdout($q->url .' => '. $url . PHP_EOL);
                $q->url = $url;
                $q->save();
            }
        }
    }

    public function actionUsersToLists()
    {
        $echo = '';
        /** @var \app\components\MailListSendPulse $ml */
        $ml = Yii::$app->maillist;
        $lists = [];
        foreach ($ml->getListsIds() as $l) {
            $lists[$l] = [];
        }
        /** @var \app\models\User[] $users */
        $users = User::find()->all();
        foreach ($users as $u) {
            $email = strtolower($u->email);
            $l = ModelsHelper::userLists($u);
            $lists[$l][] = $email;
            $lists['all'][] = $email;
        }
        foreach ($lists as $list => $emails) {
            $res = $ml->replaceEmails($list, $emails);
            $added = ArrayHelper::getValue($res, '+', []);
            $removed = ArrayHelper::getValue($res, '-', []);
            if (count($added) > 0) {
                $echo .= sprintf('%s +: %s', $list, implode(' ', $added)) . PHP_EOL;
            }
            if (count($removed) > 0) {
                $echo .= sprintf('%s -: %s', $list, implode(' ', $removed))  . PHP_EOL;
            }
        }
        if (strlen($echo) > 0) {
            echo date('r') . PHP_EOL . $echo . PHP_EOL;
        }
    }

    public function actionQueriesCounts()
    {
        /** @var \app\models\ClientQuery[] $queries */
        $queries = ClientQuery::find()->all();
        $count = count($queries);
        $c = 0;
        foreach ($queries as $q) {
            echo sprintf('%d of %d'."\r", $c++, $count);
            $dbCount = $q->dbCallInfoCount();
            if ($q->call_info_count != $dbCount) {
                $q->call_info_count = $dbCount;
                $q->save(false, ['call_info_count']);
                $this->stdout('Overwritten!' . PHP_EOL);
            }
        }
        $this->stdout(PHP_EOL);
    }

    public function actionNotifyNewNoVisits()
    {
        $scheme = '3,7,21';
        $minVisits = 100;
        $time = time();
        $days = explode(',', $scheme);
        $varId = Variable::name2Id('_notify_timestamp.newNoWidget');
        /** @var ClientSite[] $nSites */
        $nSites = [];
        foreach ($days as $k => $v) {
            $next = ArrayHelper::getValue($days, $k+1, false);
            $nextValue = $next ? $next : $v + 1;
            /** @var ClientSite[] $sites */
            $sites = ClientSite::find()
                ->andWhere(['<', 'created_at', strtotime(sprintf('-%d days', $v))])
                ->andWhere(['>', 'created_at', strtotime(sprintf('-%d days', $nextValue))])
                ->all();
            foreach ($sites as $site) {
                /** @var VariableValue $varVal */
                $varVal = VariableValue::findOne([
                    'variable_id' => $varId,
                    'user_id' => $site->user_id,
                    'site_id' => $site->id,
                    'page_id' => null,
                ]);
                $lastNotify = $varVal ? $varVal->value : 0;
                $deadLine = $site->created_at + $v * 86400;
                if ($lastNotify > $deadLine) {
                    continue;
                }
                $visitsUnique = Conversion::find()
                    ->andWhere(['user_id' => $site->user_id, 'site_id' => $site->id])
                    ->sum('visits_unique');
                if ($visitsUnique > $minVisits) {
                    continue;
                }
                $nSites[] = $site;
            }
        }
        if (($count = count($nSites)) < 1) {
            return;
        }
        $this->stdout(date('Y-m-d H:i:s ', $time));
        $this->stdout(sprintf('Found %d sites to notify' . PHP_EOL, $count));
        foreach ($nSites as $site) {
            $this->stdout(sprintf("newNoWidget notification for:\n  site: [%d] %s\n  user: [%d]%s\n",
                $site->id, $site->url, $site->user_id, $site->user->username));
            Variable::sSet($varId, $time, $site->user_id, $site->id);
        }
    }

    /**
     *
     */
    public function actionNotifyNewNoTariff()
    {
        $varName = 'u.lastNotify.siteNewInactive';
        foreach ([3, 7] as $d) {
            $sites = ClientSite::find()->newInactive($d)->all();
            echo $d .' => '. count($sites) . PHP_EOL;
            foreach ($sites as $site) {
                $deadLine = $site->created_at + $d * 86400;
                if (($lastNotify = Variable::sGet($varName, $site->user_id, $site->id)) && $lastNotify > $deadLine) {
                    continue;
                }
                echo $site->id .': '. $site->url . PHP_EOL;
                //Notification::onSite($site, 'siteNewInactive');
                Variable::sSet($varName, time(), $site->user_id, $site->id);
            }
        }
    }

    public function actionNotify()
    {
        $this->actionNotifyNewNoTariff();
    }

    public function actionNotifyDelayed($limit = false)
    {
        $lockName = 'notify-delayed';
        $lockTimeout = $limit == false ? 3600 : $limit * 33;
        if (($lock = DataHelper::lock($lockName, $lockTimeout)) !== true) {
            $this->stdout(date('Y-m-d H:i:s') .': '. Yii::t('app', 'Locked at {dt}, exiting.', ['dt' => date('Y-m-d H:i:s', $lock)]) . PHP_EOL);
            return;
        }
        $query = Notification::find()->where(['status' => Notification::STATUS_DELAYED])->orderBy(['at' => SORT_ASC])->select('id');
        if ($limit !== false) {
            $query->limit($limit);
        }
        $delayed = $query->column();
        $count = count($delayed);
        if ($count < 1) {
            DataHelper::unlock($lockName);
            return;
        }
        printf('%s: Found %d delayed notifications.' . PHP_EOL, date('Y-m-d H:i:s'), $count);
        $success = 0;
        $errors = 0;
        foreach ($delayed as $id) {
            if (!($n = Notification::findOne($id))) {
                continue;
            }
            /** @var Notification $n */
            if ($n->status === Notification::STATUS_DELAYED) {
                if ($n->send(true, false)) {
                    $success++;
                } else {
                    printf('#%d err: %s' . PHP_EOL, $n->id, $n->description);
                    $errors++;
                }
            }
        }
        printf('%s: Success: %d, errors: %d' . PHP_EOL, date('Y-m-d H:i:s'), $success, $errors);
        DataHelper::unlock($lockName);
    }

    public function actionFinishTariffs()
    {
        $lockName = 'finish-tariffs';
        if (($lock = DataHelper::lock($lockName, 3600)) !== true) {
            $this->stdout(date('Y-m-d H:i:s') .': '. Yii::t('app', 'Locked at {dt}, exiting.', ['dt' => date('Y-m-d H:i:s', $lock)]) . PHP_EOL);
            return;
        }
        $query = UserTariff::find()->select('user_id')->where(['AND',
            ['status' => UserTariff::STATUS_ACTIVE],
            ['<', UserTariffSearch::getLtCol(), time()],
            ['>', 'lifetime', 0],
        ]);
        //$sql = $query->createCommand()->rawSql;
        $ids = $query->column();
        if (($count = count($ids)) > 0) {
            $this->stdout(date('Y-m-d H:i:s') .': '. Yii::t('app', 'Found {n} active and old tariffs.', ['n' => $count]) . PHP_EOL);
            foreach ($ids as $id) {
                Tariff::userGetActive($id);
            }
        }
        DataHelper::unlock($lockName);
    }

    protected function tsLog($content)
    {
        echo date('Y-m-d H:i:s') .': '. $content;
    }

    protected function callDeferred(ClientQuery $dQuery)
    {
        $dTZ = ArrayHelper::getValue($dQuery->result, 'dTZ', '');
        if (strlen($dTZ) < 1) {
            $dTZ = ($t = Variable::sGet('u.settings.timezone', $dQuery->user_id, $dQuery->site_id, $dQuery->page_id)) ?
                $t : Yii::$app->timeZone;
        }
        $dHours = explode(',', ArrayHelper::getValue($dQuery->result, 'dHours', ''));
        if (count($dHours) < 1) {
            echo 'dHours is EMPTY!' . PHP_EOL;
            return false;
        }
        $dt = new \DateTime('@' . time(), new \DateTimeZone('UTC'));
        $dt->setTimezone(new \DateTimeZone($dTZ));
        $hour = intval($dt->format('H'));
        if (!in_array($hour, $dHours)) {
            return false;
        }

        $query = new ClientQuery();
        $props2copy = ['user_id', 'site_id', 'page_id', 'call_info'];
        foreach ($props2copy as $p) {
            $query->{$p} = $dQuery->{$p};
        }

        $this->tsLog(sprintf('Deferred query #%d (%s) : ', $dQuery->id, $dQuery->call_info));
        $query->deferred_id = $dQuery->id;
        if (!($paid = $query->checkPaid())) {
            echo 'UNPAID!' . PHP_EOL;
            return false;
        }
        if (!($rule = $query->findRule())) {
            echo 'NO RULE!' . PHP_EOL;
            return false;
        }
        $busy = ClientQuery::find()->where(['AND',
            ['rule_id' => $rule->id],
            ['>', 'at', time() - 600],
            ['status' => [
                ClientQuery::STATUS_INIT,
                ClientQuery::STATUS_CALLS_INIT,
                ClientQuery::STATUS_POOL_INIT,
                ClientQuery::STATUS_CLIENT_INIT,
                ClientQuery::STATUS_COMM_INIT,
            ]],
        ])->exists();
        if ($busy) {
            echo sprintf('Rule #%d BUSY.', $rule->id) . PHP_EOL;
            return false;
        }
        if (!$query->save()) {
            echo 'SAVE FAILED!' . PHP_EOL;
            return false;
        }
        printf('new query #%d => with rule #%d : ', $query->id, $rule->id);
        if (!$query->process()) {
            echo 'FAILED!' . PHP_EOL;
            return false;
        }
        $query->save();
        echo 'OK.' . PHP_EOL;
        return $query;
    }

    public function actionCallDeferred()
    {
        $lockName = 'deferred-calls';
        if (($lock = DataHelper::lock($lockName, 3600)) !== true) {
            $this->tsLog(Yii::t('app', 'Locked at {dt}, exiting.', ['dt' => date('Y-m-d H:i:s', $lock)]) . PHP_EOL);
            return;
        }

        $now = time();
        $at = $now - 86400 * 3;

        $exclude = ClientQuery::find()
            ->from(['e' => '{{%client_query}}'])
            ->where(['AND',
                '`e`.`at` > ' . $at,
                '`e`.`at` > `q`.`at`',
                '`q`.`call_info` = `e`.`call_info`',
                ['OR',
                    ['status' => [
                        ClientQuery::STATUS_UNPAID,
                        ClientQuery::STATUS_DEFERRED,
                        ClientQuery::STATUS_INIT,
                        ClientQuery::STATUS_CALLS_INIT,
                        ClientQuery::STATUS_POOL_INIT,
                        ClientQuery::STATUS_CLIENT_INIT,
                        ClientQuery::STATUS_COMM_INIT,
                    ]],
                    ['>', 'status', ClientQuery::STATUS_POOL_FAILED]
                ],
            ]);

        $queries = ClientQuery::find()
            ->from(['q' => '{{%client_query}}'])
            ->where(['AND',
                ['status' => ClientQuery::STATUS_DEFERRED],
                ['>', 'at', $at],
                ['NOT EXISTS', $exclude],
            ]);

//        $sql = $queries->createCommand()->rawSql;
//        echo $sql . PHP_EOL;

        $queries = $queries->all();
        /** @var ClientQuery[] $queries */
        if (($count = count($queries)) > 0) {

            $this->tsLog(sprintf('Deferred queries: %d' . PHP_EOL, $count));
            $skipped = [];
            $done = [];
            $failed = [];
            foreach ($queries as $q) {

                $tries = $q->getDeferredQueries()->count();
                $maxTries = Variable::sGet('w.options.deferredTries', $q->user_id, $q->site_id, $q->page_id);
                if ($tries >= $maxTries) {
                    $failed[] = $q->id;
                    $q->status = ClientQuery::STATUS_DEFERRED_FAILED;
                    $q->save(false, ['status']);
                    continue;
                }

                $res = $this->callDeferred($q);
                if ($res === false) {
                    $skipped[] = $q->id;
                } else if ($res instanceof ClientQuery) {
                    $done[] = $q->id .':'. $res->id;
                }
            }
            $this->tsLog(sprintf('Done: %d [%s], skipped: %d [%s], failed: %d [%s]' . PHP_EOL,
                count($done), implode(', ', $done),
                count($skipped), implode(', ', $skipped),
                count($failed), implode(', ', $failed)
            ));

        }


        DataHelper::unlock($lockName);
    }

    public function actionCheckCodes($timeout = 3600)
    {
        $ts = time() - $timeout;
        $sites = ClientSite::find()->where(['AND',
//            ['NOT IN', 'user_id', User::find()->select('id')->where(['IS NOT', 'blocked_at', null])],
            ['<', 'w_checked_at', $ts],
//            ['>', 'w_check_result', -100],
        ])->all();

        printf('Found %d sites to check' . PHP_EOL, count($sites));
        foreach ($sites as $site) {
            echo $site->url . ': ';
            $res = DataHelper::checkWidgetCodeSave($site);
            if ($res > 0) {
                $this->stdout('FOUND', Console::FG_GREEN);
            } else if ($res == 0) {
                $this->stdout('NOT FOUND!', Console::FG_RED);
            } else {
                $this->stdout('FAIL!', Console::BG_RED);
            }
            echo "\n";
        }
    }

    public function actionClearVisits($limit = 14)
    {
        $time = time();
        $db = Yii::$app->db;
        $timeLimit = $time - 86400 * $limit;
        echo date('Y-m-d H:i:s') . ': Clearing visits: ';
        $sqlSub = 'SELECT * FROM {{%client_query}} WHERE {{%client_visit}}.`id` = `visit_id`';
        $sql = 'DELETE FROM {{%client_visit}} WHERE at <= ' . $timeLimit . ' AND NOT EXISTS (' . $sqlSub . ')';
        $cmd = $db->createCommand($sql);
        //echo $cmd->rawSql . PHP_EOL;
        $deleted = $cmd->execute();
        printf('%d rows deleted in %.2fs. optimizing: ', $deleted, time() - $time);
        $time = time();
        $db->createCommand('OPTIMIZE TABLE {{%client_query}}')->execute();
        printf('done in %.2fs' . PHP_EOL, time() - $time);
    }

    public function actionMoveSiteData($fromId, $toId)
    {
        $db = Yii::$app->db;
        $cmd = $db->createCommand();
        $tables = $db->schema->tableSchemas;
        $tr = $db->beginTransaction();
        foreach ($tables as $tbl) {
            $cols = $tbl->columns;
            if (isset($cols['site_id'])) {
                $this->stdout($tbl->name . ': ');
                $res = $cmd->update($tbl->name, ['site_id' => $toId], ['site_id' => $fromId])->execute();
                $this->stdout($res . PHP_EOL);
            }
        }
        $tr->commit();
    }

    public function actionDeleteSite($id)
    {
        /** @var ClientSite $site */
        if (!($site = ClientSite::findOne($id))) {
            $this->stdout('Site not found.' . PHP_EOL);
            return;
        }
        $this->stdout('title: ' . $site->title . PHP_EOL);
        $this->stdout('domain: ' . $site->domain . PHP_EOL);
        $this->stdout('url: ' . $site->url . PHP_EOL);
        $this->stdout('owner: ' . $site->user_id .' - '. $site->user->username . PHP_EOL);
        if (!$this->confirm('Are you sure you want to delete this site?')) {
            return;
        }
        $db = Yii::$app->db;
        $cmd = $db->createCommand();
        $tables = $db->schema->tableSchemas;
        $tr = $db->beginTransaction();
        foreach ($tables as $tbl) {
            $cols = $tbl->columns;
            if (isset($cols['site_id'])) {
                $this->stdout($tbl->name . ': ');
                $res = $cmd->delete($tbl->name, ['site_id' => $id])->execute();
                $this->stdout($res . PHP_EOL);
            }
        }
        $site->delete();
        $tr->commit();
    }

    public function actionFixUrls()
    {
        $sites = ClientSite::find()->all();
        foreach ($sites as $site) {
            $url = $site->url;
            $domain = $site->domain;
            $info = $url .' ['. $domain .'] ';
            if ($site->validate()) {
                if ($site->url == $url && $site->domain == $domain) {
//                    $this->stdout('Valid unchanged.', Console::FG_GREEN);
                } else {
                    echo $info;
                    $this->stdout('Valid changed to: ' . PHP_EOL, Console::FG_YELLOW);
                    $this->stdout($site->url .' ['. $site->domain .'] ', Console::FG_CYAN);
                    if ($site->save(false)) {
                        $this->stdout('Saved', Console::FG_GREEN);
                    } else {
                        $this->stdout('Unsaved', Console::FG_RED);
                    }
                    $this->stdout(PHP_EOL);
                }
            } else {
                echo $info;
                $this->stdout('Invalid: ' . PHP_EOL, Console::FG_RED);
                foreach ($site->getErrors() as $attribute => $errors) {
                    foreach ($errors as $error) {
                        $this->stdout($attribute .': '. $error . PHP_EOL, Console::FG_RED);
                    }
                }
            }
        }
    }

}
