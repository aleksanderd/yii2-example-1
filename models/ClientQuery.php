<?php

namespace app\models;

use app\base\tplModel;
use app\helpers\VoxImplant;
use app\models\variable\CSettings;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%client_query}}".
 *
 * @property integer $id
 * @property integer $status
 * @property integer $test_id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $page_id
 * @property integer $rule_id
 * @property integer $user_tariff_id
 * @property integer $at
 * @property integer $time
 * @property integer $record_time
 * @property string $url
 * @property string $call_info
 * @property integer $call_info_count
 * @property string $record_data
 * @property string $result_data
 * @property string $data
 * @property float $client_cost
 * @property float $cost
 * @property integer $hit_id
 * @property integer $visit_id
 * @property integer $deferred_id
 * @property integer $visit_time
 * @property integer $hit_time
 * @property integer $trigger
 *
 * @property-read integer $datetime_period
 * @property string $statusLabel
 * @property string $callInfo // *** если минусовой баланс
 * @property bool $isSuccess
 * @property ClientRule $rule
 * @property UserTariff $userTariff
 * @property ClientQueryTest $test
 * @property ClientSite $site
 * @property ClientPage $page
 * @property User $user
 * @property ClientQueryCall[] $calls
 * @property Notification[] $notifications
 * @property WidgetHit $hit
 * @property ClientVisit $visit
 * @property ClientQuery $deferred
 * @property ClientQuery[] $deferredQueries
 */
class ClientQuery extends \yii\db\ActiveRecord
{

    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    /** Недостаточно средств у пользователя для обработки запроса. */
    const STATUS_UNPAID = -1000;

    /** Свеже-созданный запрос. Начальное состояние. */
    const STATUS_INIT = 0;

    /** Отложенный звонок */
    const STATUS_DEFERRED = 10;
    const STATUS_DEFERRED_FAILED = 11;

    /** Не найдено правило. */
    const STATUS_RULE_NOT_FOUND = 19;
    /** Найдено подходящее правило. */
    const STATUS_RULE_FOUND = 20;

    /** Не удалось запустить процесс созвона. */
    const STATUS_CALLS_INIT_FAILED = 49;
    /** Запущен процесс созвона. */
    const STATUS_CALLS_INIT = 50;
    /** Ошибка при выполнения сценария. */
    const STATUS_CALLS_ERROR = 51;

    /** Начали звонить менеджерам. */
    const STATUS_POOL_INIT = 100;
    /** Начали звонить менеджерам... но, не дозвонились. */
    const STATUS_POOL_FAILED = 101;
    /** Дозвонились менеджеру. */
    const STATUS_POOL_CONN = 190;
    /** Дозвонились менеджеру... но, оборвалось. */
    const STATUS_POOL_DISCONN = 191;

    /** Начали звонить клиенту. */
    const STATUS_CLIENT_INIT = 200;
    /** Начали звонить клиенту... но, не дозвонились. */
    const STATUS_CLIENT_FAILED = 201;
    /** Дозвонились клиенту. */
    const STATUS_CLIENT_CONN = 290;
    /** Дозвонились клиенту... но, оборвалось. */
    const STATUS_CLIENT_DISCONN = 291;

    /** Начали соединять менеджера с клиентом. */
    const STATUS_COMM_INIT = 900;
    /** Начали соединять менеджера с клиентом, но не получилось. */
    const STATUS_COMM_FAILED = 901;
    /** Успешно соеденили менеджера с клиентом. */
    const STATUS_COMM_SUCCESS = 1000;
    /** Успешно соеденили менеджера с клиентом. Менеджер завершил связь. */
    const STATUS_COMM_POOL_DISCONN = 1001;
    /** Успешно соеденили менеджера с клиентом. Клиент завершил связь. */
    const STATUS_COMM_CLIENT_DISCON = 1002;

    public $customData, $record, $result;

    public static function statusLabels()
    {
        return [
            static::STATUS_UNPAID => Yii::t('app', 'Unpaid call query'),

            static::STATUS_INIT => Yii::t('app', 'Query init'),
            static::STATUS_DEFERRED => Yii::t('app', 'Query deferred'),
            static::STATUS_DEFERRED_FAILED => Yii::t('app', 'Deferred failed'),
            static::STATUS_RULE_NOT_FOUND => Yii::t('app', 'Rule not found'),
            static::STATUS_RULE_FOUND => Yii::t('app', 'Rule found'),

            static::STATUS_CALLS_INIT_FAILED => Yii::t('app', 'Start calls failed'),
            static::STATUS_CALLS_INIT => Yii::t('app', 'Start calls'),
            static::STATUS_CALLS_ERROR => Yii::t('app', 'Calls error'),

            static::STATUS_POOL_INIT => Yii::t('app', 'Call managers'),
            static::STATUS_POOL_FAILED => Yii::t('app', 'Call managers failed'),
            static::STATUS_POOL_CONN => Yii::t('app', 'Manager connected'),
            static::STATUS_POOL_DISCONN => Yii::t('app', 'Manager disconnected'),

            static::STATUS_CLIENT_INIT => Yii::t('app', 'Call customer'),
            static::STATUS_CLIENT_FAILED => Yii::t('app', 'Call customer failed'),
            static::STATUS_CLIENT_CONN => Yii::t('app', 'Customer connected'),
            static::STATUS_CLIENT_DISCONN => Yii::t('app', 'Customer disconnected'),

            static::STATUS_COMM_INIT => Yii::t('app', 'Connect customer to manager'),
            static::STATUS_COMM_FAILED => Yii::t('app', 'Connect customer to manager failed'),
            static::STATUS_COMM_SUCCESS => Yii::t('app', 'Customer and manager connected'),
            static::STATUS_COMM_POOL_DISCONN => Yii::t('app', 'Call finished by manager'),
            static::STATUS_COMM_CLIENT_DISCON => Yii::t('app', 'Call finished by customer'),
        ];
    }

    public function getDatetime_period()
    {
        return $this->at;
    }

    public function getCallInfo()
    {
        /** @var \app\models\User $user */
        $user = isset(Yii::$app->user, Yii::$app->user->identity) ? Yii::$app->user->identity : $this->user;
        if (!$user || $user->isAdmin || $user->isPaid) {
            return $this->call_info;
        } else {
            $value = $this->call_info;
            for ($i = 2, $l = strlen($value) - 3; $i < $l; $i++) {
                $value[$i] = '*';
            }
            return $value;
        }
    }

    public function getIsSuccess()
    {
        return $this->status >= static::STATUS_COMM_SUCCESS;
    }

    public function getStatusLabel()
    {
        return ArrayHelper::getValue(static::statusLabels(), $this->status, '-');
    }

    public function init()
    {
        if (!isset($this->at)) {
            $this->at = time();
        }
        parent::init();
    }

    public function afterFind()
    {
        $this->customData = unserialize($this->data);
        $this->record = unserialize($this->record_data);
        $this->result = unserialize($this->result_data);
        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (is_array($this->customData)) {
            $this->data = serialize($this->customData);
        }
        if (is_array($this->record)) {
            $this->record_data = serialize($this->record);
        }
        if (is_array($this->result)) {
            $this->result_data = serialize($this->result);
        }
        if (!isset($this->call_info_count) || $this->call_info_count < 1) {
            $this->call_info_count = $this->dbCallInfoCount();
        }
        return parent::beforeSave($insert);
    }

    public function updateTime()
    {
        $this->time = time() - $this->at;
        return $this;
    }

    public function dbCallInfoCount()
    {
        if (!isset($this->user_id, $this->site_id)) {
            return 1;
        }
        return 1 + ClientQuery::find()->where([
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'call_info' => $this->call_info,
        ])->andWhere(['<', 'at', $this->at])->count();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_query}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'status', 'test_id', 'user_id', 'site_id', 'page_id', 'rule_id', 'hit_id', 'visit_id', 'at', 'time', 'record_time',
                'visit_time', 'hit_time', 'trigger',
            ], 'integer'],
            [['client_cost', 'cost'], 'number'],
            [['url', 'data'], 'string'],
            [['call_info'], 'string', 'max' => 70]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'status' => Yii::t('app', 'Status'),
            'test_id' => Yii::t('app', 'Test ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'site_id' => Yii::t('app', 'Website ID'),
            'page_id' => Yii::t('app', 'Page ID'),
            'rule_id' => Yii::t('app', 'Rule ID'),
            'user_tariff_id' => Yii::t('app', 'User tariff ID'),
            'hit_id' => Yii::t('app', 'Hit ID'),
            'visit_id' => Yii::t('app', 'Visit ID'),
            'at' => Yii::t('app', 'At'),
            'time' => Yii::t('app', 'Time'),
            'record_time' => Yii::t('app', 'Record time'),
            'record' => Yii::t('app', 'Record'),
            'result' => Yii::t('app', 'Result'),
            'call_info' => Yii::t('app', 'Call info'),
            'call_info_count' => Yii::t('app', 'Call info count'),
            'callInfo' => Yii::t('app', 'Call info'),
            'data' => Yii::t('app', 'Data'),
            'client_cost' => Yii::t('app', 'Cost'),
            'cost' => Yii::t('app', 'VI Cost'),
            'deferred_id' => Yii::t('app', 'Deferred query'),
            'deferred' => Yii::t('app', 'Deferred query'),
            'visit_time' => Yii::t('app', 'Visit Time'),
            'hit_time' => Yii::t('app', 'Hit Time'),
            'trigger' => Yii::t('app', 'Trigger'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(ClientRule::className(), ['id' => 'rule_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTariff()
    {
        return $this->hasOne(UserTariff::className(), ['id' => 'user_tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTest()
    {
        return $this->hasOne(ClientQueryTest::className(), ['id' => 'test_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(ClientSite::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(ClientPage::className(), ['id' => 'page_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalls()
    {
        return $this->hasMany(ClientQueryCall::className(), ['query_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::className(), ['query_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHit()
    {
        return $this->hasOne(WidgetHit::className(), ['id' => 'hit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(ClientVisit::className(), ['id' => 'visit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeferred()
    {
        return $this->hasOne(ClientQuery::className(), ['id' => 'deferred_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeferredQueries()
    {
        return $this->hasMany(ClientQuery::className(), ['deferred_id' => 'id']);
    }

    /**
     * Возвращает true если есть активный тариф или баланс больше нуля. При этом, тариф присваивается в user_tariff_id
     * @return bool
     */
    public function checkPaid()
    {
        if ($tariff = Tariff::userGetActive($this->user_id, true, $this)) {
            $this->user_tariff_id = $tariff->id;
            return true;
        }
        return $this->user->balance > 0;
    }

    /**
     * Поиск подходящего правила для данного запроса.
     * @param bool $apply Применить или нет к данному запросу найденное правило
     * @param array|bool $debug Переменная для сохранения отладочной информации, если не нужно - false
     * @return ClientRule|bool Правило или false, в случае неудачи
     */
    public function findRule($apply = true, &$debug = false)
    {
        $result = false;
        $dbRules = ClientRule::find()->where(['AND',
            ['user_id' => $this->user_id],
            ['>', 'active', 0],
            ['OR',
                ['site_id' => $this->site_id],
                ['site_id' => null]
            ],
            ['OR',
                ['page_id' => $this->page_id],
                ['page_id' => null]
            ],
        ])->orderBy([
            'user_id' => SORT_DESC,
            'site_id' => SORT_DESC,
            'page_id' => SORT_DESC,
            'priority' => SORT_DESC,
        ]);
        if (is_array($debug)) {
            $debug['sql'] = $dbRules->createCommand()->rawSql;
        }

        foreach ($dbRules->all() as $rule) {
            /** @var ClientRule $rule */
            $ruleDebug = is_array($debug) ? [] : false;
            $res = $rule->checkQuery($this, $ruleDebug);
            if (is_array($debug)) {
                $debug['rules'][] = [
                    'rule' => $rule,
                    'debug' => $ruleDebug,
                ];
            }
            if ($res) {
                $result = $rule;
                break;
            }
        }
        if ($apply && $result) {
            $this->status = self::STATUS_RULE_FOUND;
            $this->rule_id = $result->id;
        }
        if (is_array($debug)) {
            $debug['result'] = $result;
        }
        return $result;
    }

    /**
     * Запускает процесс связи (обращение к *VoxImplant*).
     * У запроса уже должно быть определено подходящее правило.
     * Данные(список линий) из правила передаются с запросом на *VoxImplant*.
     * В записи запроса сохраняются данные сессии для дальнейшего общения с *VoxImplant*'ом.
     * @param bool $debug Переменная для сохранения отладочной информации, если не нужно - false
     * @return bool true в случае успеха, иначе - false
     */
    public function process(&$debug = false)
    {
        /** @var ClientRule $rule */
        $rule = $this->getRule()->one();
        if (!$rule) {
            return false;
        }

        $lines = $rule->getLines();
        if (is_array($debug)) {
            $debug['sql'] = $lines->createCommand()->rawSql;
        }
        $linesData = [];
        foreach ($lines->all() as $line) {
            $linesData[] = [
                'id' => $line->id,
                'info' => $line->info,
            ];
        }

        if (sizeof($linesData) < 1) {
            return false;
        }
        if (is_array($debug)) {
            $debug['lines'] = $linesData;
        }

        $cSettings = (new CSettings([
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'page_id' => $this->page_id,
        ]))->getValues();

        $baseUrl = Variable::sGet('s.settings.baseUrl');
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        $this->status = self::STATUS_CALLS_INIT;
        $vi = new VoxImplant();
        $dbg = is_array($debug) ? [] : false;
        $data = [
            'query_id' => $this->id,
            'call_info' => $this->call_info,
            'lines' => $linesData,
            'settings' => $cSettings,
//                'gwUrl' => Url::toRoute(['/gw'], true),
            'gwUrl' => $baseUrl . 'gw',
        ];
        $res = $vi->startScenarios(['script_custom_data' => $data], $dbg);

        if ($mUrl = ArrayHelper::getValue($res, 'media_session_access_url')) {
            $this->customData['media_session_access_url'] = $mUrl;
        }

        if (is_array($debug)) {
            $debug['vi']['startScenarios']['res'] = $res;
            if (sizeof($dbg) > 0) {
                $debug['vi']['startScenarios'] = array_merge($debug['vi']['startScenarios'], $dbg);
            }
        }
        /*
                $dbg = is_array($debug) ? [] : false;
                $res = $vi->sessionExec([
                    'testParam1' => 'testValue1'
                ], $dbg);

                if (is_array($debug)) {
                    $debug['vi']['sessionExec']['res'] = $res;
                    if (sizeof($dbg) > 0) {
                        $debug['vi']['sessionExec'] = array_merge($debug['vi']['sessionExec'], $dbg);
                    }
                }
        */
        return true;
    }

    /**
     * Возвращает массив строк для замены в шаблонах.
     *
     * Поля непосредственно из таблицы БД:
     * * {id}
     * * {status} - Целочисленное!
     * * {user_id}
     * * {site_id}
     * * {page_id}
     * * {rule_id}
     * * {hit_id}
     * * {user_tariff_id}
     * * {at} - Целочисленное! Unix-timestamp.
     * * {time} - время выполнения запроса.
     * * {url} - адрес откуда пришел запрос.
     * * {call_info} - инфа для связи с клиентом.
     * * {record_time} - длительность записи разговора.
     * * {client_cost} - стоимость запроса (клиентская)
     *
     * Другие поля:
     * * {timezone} - Временная зона из настроек пользователя.
     * * {datetime} - Строковое выражение текущего времени в выбранной временной зоне.
     * * {datetime.utc} - Строковое выражение текущего времени в зоне UTC.
     * * {callInfo} - инфа для связи с клиентом, но скрытая звёздочками, если баланс юзера <0.
     *
     * Кроме того, можно использовать поля из связанных моделей, обращаясь к ним через используя префиксы:
     * * [[User]]
     *   * {user.username}
     *   * {user.email}
     *   * {user.balance}
     *   * ...и тд. см. [[User::tplPlaceholders()]].
     * * [[ClientSite]]
     *   * {site.id}
     *   * {site.title}
     *   * {site.description}
     *   * {site.url}
     * * [[ClientPage]]
     *   * {page.id}
     *   * {page.title}
     *   * {page.pattern}
     * * [[ClientRule]]
     *   * {rule.id}
     *   * {rule.title}
     *   * {rule.description}
     * * [[ClientLine]]
     *   * {line.id}
     *   * {line.title}
     *   * {line.info}
     *   * {line.description}
     * * [[UserTariff]]
     *   * {tariff.id}
     *   * {tariff.title}
     *   * ...и тд, подробнее: [[UserTariff::tplPlaceholders()]].
     *
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);

        if ($user = $this->user) {
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }
        if ($site = $this->site) {
            $result = array_merge($result, $site->tplPlaceholders($prefix . 'site.'));
        }
        if ($page = $this->page) {
            $result = array_merge($result, $page->tplPlaceholders($prefix . 'page.'));
        }
        if ($page = $this->userTariff) {
            $result = array_merge($result, $page->tplPlaceholders($prefix . 'tariff.'));
        }
        $tz = '';
        if ($rule = $this->rule) {
            $tz = $rule->timezone;
            $result = array_merge($result, $rule->tplPlaceholders($prefix . 'rule.'));
            $line = $rule->lines;
            if (is_array($line)) {
                $line = $line[0];
            }
            /** @var ClientLine $line */
            $result = array_merge($result, $line->tplPlaceholders($prefix . 'line.'));
        }
        if (strlen($tz) < 1) {
            if (!($tz = Variable::sGet('u.settings.timezone', $this->user_id, $this->site_id, $this->page_id))) {
                $tz = Yii::$app->timeZone;
            }
        }
        $result = array_merge($result, static::tplDatetimePlaceholders($this->at, $tz));
        $result['{'.$prefix.'statusLabel}'] = $this->getStatusLabel();
        if (isset($this->record['url'])) {
            $result['{'.$prefix.'record.url}'] = $this->record['url'];
        }

        $url = ArrayHelper::getValue($result, '{'.$prefix.'url}', '');
        if (($sUrl = strstr($url, '?', true)) != false) {
            $result['{'.$prefix.'url}'] = $sUrl;
        }

        $result['{'.$prefix.'callInfo}'] = $this->callInfo;

        return $result;
    }

}
