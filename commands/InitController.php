<?php

namespace app\commands;

use Yii;
use app\models\ClientLine;
use app\models\ClientRule;
use app\models\ClientSite;
use app\models\Variable;
use app\models\User;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

define('TM_WEEK_ALL', 'tm_week_all');
define('TM_WEEK_WORK', 'tm_week_work');
define('TM_DAY_ALL', 'tm_day_all');
define('TM_DAY_WORK', 'tm_day_work');

class InitController extends Controller {

    public static function getTmPreset($what)
    {
        switch($what) {
            case TM_WEEK_ALL:
                return [0, 1, 2, 3, 4, 5, 6];
            case TM_WEEK_WORK:
                return [1, 2, 3, 4, 5];
            case TM_DAY_ALL:
                return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
            case TM_DAY_WORK:
                return [8, 9, 10, 11, 13, 14, 15, 16];
            default:
                return null;
        }
    }

    public function createVariable($config)
    {
        /** @var Variable $variable */
        if ($variable = Variable::findOne($config)) {
            $this->stdout('Variable ' . $variable->name . '  => ' . $variable->id .  "\n", Console::FG_GREY);
            return $variable;
        }
        $this->stdout('Creating variable ' . $config['name'] . ': ');
        $variable = new Variable($config);
        if ($variable->save()) {
            $this->stdout("ok\n", Console::FG_GREEN);
            return $variable;
        } else {
            $this->stdout("FAILED!\n", Console::FG_RED);
            return false;
        }
    }

    public function createUser($username, $password)
    {
        if ($user = User::findOne(['username' => $username])) {
            $this->stdout('User ' . $username . '  => ' . $user->id .  "\n", Console::FG_GREY);
            return $user;
        }
        $this->stdout('Creating user ' . $username . ': ');
        $user = new User([
            'scenario' => 'create',
            'username' => $username,
            'password' => $password,
            'email' => $username . '@gmcf.lo',
        ]);
        if ($user->save()) {
            $this->stdout("ok\n", Console::FG_GREEN);
            $user->confirm();
            return $user;
        } else {
            $this->stdout("FAILED!\n", Console::FG_RED);
            return false;
        }
    }

    public function createSite($user, $config)
    {
        if (!(isset($config['title']) && isset($config['url']))) {
            $this->stdout('ERROR: title and url are required for a site' . "\n", Console::FG_RED);
            return false;
        }
        $config['user_id'] = $user->id;
        if ($site = ClientSite::findOne($config)) {
            $this->stdout('Site ' . $config['title'] . ' => ' . $site->id .  "\n", Console::FG_GREY);
            return $site;
        }
        $this->stdout('Creating site "' . $config['title'] . '": ');
        if (!isset($config['description'])) {
            $config['description'] = 'More ' . $config['title'] . ' description';
        }
        $site = new ClientSite($config);
        if ($site->save()) {
            $this->stdout("ok\n", Console::FG_GREEN);
            return $site;
        } else {
            $this->stdout("FAILED!\n", Console::FG_RED);
            return false;
        }
    }

    public function createLine($user, $config)
    {
        $config['user_id'] = $user->id;
        if ($line = ClientLine::findOne($config)) {
            $this->stdout('Line ' . $config['title'] . ' => ' . $line->id .  "\n", Console::FG_GREY);
            return $line;
        }
        if (!isset($config['description'])) {
            $config['description'] = 'Description for "' . $config['title'] . '" line [' . $config['info'] . ']';
        }
        $this->stdout('Creating line "' . $config['title'] . '": ');
        $line = new ClientLine($config);
        if ($line->save()) {
            $this->stdout("ok\n", Console::FG_GREEN);
            return $line;
        } else {
            $this->stdout("FAILED!\n", Console::FG_RED);
            return false;
        }
    }

    public function createRule($user, $site, $config)
    {
        $config['user_id'] = $user->id;
        $config['site_id'] = isset($site->id) ? $site->id : null;
        $fConfig = $config;
        if ($rule = ClientRule::findOne($fConfig)) {
            $this->stdout('Rule ' . $config['title'] . ' => ' . $rule->id .  "\n", Console::FG_GREY);
            return $rule;
        }
        $this->stdout('Creating rule "' . $config['title'] . '": ');
        $rule = new ClientRule($config);
        if ($rule->save()) {
            $this->stdout("ok\n", Console::FG_GREEN);
            return $rule;
        } else {
            $this->stdout("FAILED!\n", Console::FG_RED);
            return false;
        }
    }

    public function actionTest()
    {
        $users = ['fly' => [
            'sites' => [
                [
                    'title' => 'fly\'s test Site 1',
                    'url' => 'http://flys-test-site1.lo/',
                    'rules' => [
                        [
                            'title' => 's1 Default rule (accept all)',
                            'priority' => 0,
                        ],
                        [
                            'title' => 's1 Work time rule (work day & hour)',
                            'priority' => 1000,
                        ],
                    ],
                ],
            ],
            'lines' => [
                [
                    'title' => 'Домашний',
                    'info' => '+78315224426',
                ],
                [
                    'title' => 'Билайн',
                    'info' => '+79058686879',
                ],
                [
                    'title' => 'Ростелеком',
                    'info' => '+79023053503',
                ],
            ],
        ], 'admin'];
        foreach ($users as $k => $v) {
            if (is_array($v)) {
                $user = $this->createUser($k, $k . '1test');
                $lines = ArrayHelper::getValue($v, 'lines', []);
                foreach ($lines as $lConfig) {
                    $line = $this->createLine($user, $lConfig);
                }
                $sites = ArrayHelper::getValue($v, 'sites', []);
                foreach ($sites as $sConfig) {
                    $rules = ArrayHelper::remove($sConfig, 'rules', []);
                    $site = $this->createSite($user, $sConfig);
                    foreach ($rules as $rConfig) {
                        $rule = $this->createRule($user, $site, $rConfig);
                    }
                }
            } else {
                $this->createUser($v, $v . '1test');
            }
        }
    }

    public function actionVars()
    {
        // Инициализация переменных через предусмотренные модели (VariableModel)
        $models = [
            'CSettings' => [
                'voiceType' => 'RU_RUSSIAN_FEMALE',
                'mClientCallFailed' => 'К сожалению, соединение не может быть установлено',
                'mIncomingCall' => 'Примите звонок с сайта',
            ],
            'SPrice' => [
                'callMinute' => 9.99,
                'sms' => 0,
                'email' => 0,
            ],
            'SNotify' => [
                'emailFrom' => '',
                'smsFrom' => '',
                'emailTo' => '',
                'smsTo' => '',
                'userNew' => 0,
                'siteNew' => 0,
                'userNewEmailSubject' => 'New user added',
                'userNewEmailBody' => 'New user added',
                'userNewSmsBody' => 'New user added',
                'siteNewEmailSubject' => 'New site added',
                'siteNewEmailBody' => 'New site added',
                'siteNewSmsBody' => 'New site added',
            ],
            'UNotify' => [
                'emailFrom' => 'noreply@gmcf.ru',
                'smsFrom' => '',
                'emailTo' => '',
                'smsTo' => '',

                'querySuccess' => 0,
                'queryFail' => 3,
                'querySuccessEmailSubject' => "Call query from {url}",
                'querySuccessEmailBody' =>
                    "Url: {url}\n" .
                    "Status: {status} - {statusLabel}\n" .
                    "Record: {record.url}\n" .
                    "{timezone}: {datetime}\n" .
                    "UTC: {datetime.utc}\n" .
                    "Site: {site.id}. '{site.title}'\n" .
                    "Rule: {rule.id}. '{rule.title}'\n" .
                    "Line: {line.id}. '{line.title}', {line.info}\n",
                'querySuccessSmsBody' =>
                    "Url: {url}\n" .
                    "Status: {status} - {statusLabel}\n" .
                    "{timezone}: {datetime}\n" .
                    "UTC: {datetime.utc}\n" .
                    "Site: {site.id}. '{site.title}'\n" .
                    "Rule: {rule.id}. '{rule.title}'\n" .
                    "Line: {line.id}. '{line.title}', {line.info}\n",
                'queryUnpaidEmailSubject' => "Missed call query from {url}",
                'queryUnpaidEmailBody' =>
                    "Url: {url}\n" .
                    "Status: {status} - {statusLabel}\n" .
                    "{timezone}: {datetime}\n" .
                    "UTC: {datetime.utc}\n" .
                    "Site: {site.id}. '{site.title}'\n",
                'queryUnpaidSmsBody' =>
                    "Missed call query from {url}\n" .
                    "Status: {status} - {statusLabel}\n" .
                    "{timezone}: {datetime}\n",
                'minBalance' => 3,
                'minBalanceValue' => 100,
                'minBalanceEmailSubject' => "Balance for \"{username}\" is {balance}",
                'minBalanceEmailBody' =>
                    "{datetime}\n" .
                    "Balance for \"{username}\" is {balance}\n",
                'minBalanceSmsBody' => "Balance for \"{username}\" is {balance}",
                'tariffEnd' => 3,
                'tariffEndEmailSubject' => "Tariff \"{title}\" finished",
                'tariffEndEmailBody' =>
                    "Tariff \"{title}\" finished at {finishedDatetime}.\n" .
                    "Lifetime: {lifetimeText}\n" .
                    "Minutes used: {minutes_used} of {minutes}\n",
                'tariffEndSmsBody' =>
                    "Tariff \"{title}\" finished.",
                'tariffRenewFail' => 3,
                'tariffRenewFailEmailSubject' => "Tariff \"{title}\" renew failed",
                'tariffRenewFailEmailBody' =>
                    "{datetime}\n" .
                    "Tariff \"{title}\" renew failed.\n" .
                    "Lifetime: {lifetimeText}\n" .
                    "Minutes used: {minutes_used} of {minutes}\n",
                'tariffRenewFailSmsBody' =>
                    "Tariff \"{title}\" renew failed.",

            ],
            'USettings' => [
                'language' => 'en',
                'timezone' => 'Europe/Moscow',
                'pageAnimation' => 'bounceInRight',
            ],
            'WSettings' => [
                'language' => 'en',
                'style' => 'default',
                'styleColor' => 'default',
                'styleDirection' => 'default',
                'btnStyle' => 'default',
                'restoreInfo' => 0,
                'defaultPrefix' => '+7',
                'startDelay' => 0,
                'forcedModalDelay' => 0,
                'pageEndAction' => 'ignore',
                'pageEndPercent' => 90,
                'selectionAction' => 'ignore',
                'selectionDelay' => 3,
                'selectionMin' => 1,
            ],
            'WTextsEn' => [
                'modalTitle' => 'Would you like us to call you in 26 seconds?',
                'modalClose' => 'No, thanks',
                'modalDescription' => '',
                'modalNotice' =>
                    'Powered by <a href="http://getmorecustomersfast.com" target="_blank">getmorecustomersfast.com</a><br>' .
                    'Your call may be monitored and recorded for quality assurance purposes.',
                'modalSupShow' => 'append',
                'modalSupAvail' => 'Number of our support heros available to help you right now',
                'modalSupBusy' => 'Number of our support heros busy helping others',
                'modalSupHelped' => 'Number of people we helped today',
                'modalInputPlaceholder' => 'Please enter your phone number',
                'modalSubmit' => 'Call me!',
                'modalStatusInit'=> 'Enter the number (10 digits)',
                'modalStatusInputTip' => 'Enter all 10 digits of your number',
                'modalStatusInputOk' => 'The number seems to be ok, just press the button!',
                'modalStatusRequest' => 'Call request sent... ',
                'modalStatusCallMan' => 'Calling the manager... ',
                'modalStatusZeroDef' => 'Awaiting the call please... if no one for a while, feel free to retry.',
                'error' => 'Error!',
                'success' => 'Success!',
            ],
            'WTextsRu' => [
                'modalTitle' => 'Хотите чтобы мы позвонили Вам?',
                'modalClose' => 'Нет, спасибо',
                'modalDescription' => '',
                'modalNotice' =>
                    'При поддержке сервиса <a href="http://getmorecustomersfast.com" target="_blank">getmorecustomersfast.com</a><br>' .
                    'Ваш звонок может быть записан и исследован в целях обеспечения качества услуг.',
                'modalSupAvail' => 'Количество наших менеджеров готовых помочь Вам прямо сейчас',
                'modalSupBusy' => 'Количество наших менеджеров занятых помощью другим',
                'modalSupHelped' => 'Количество людей которым мы уже помогли сегодня',
                'modalInputPlaceholder' => 'Введите номер телефона здесь',
                'modalSubmit' => 'Позвоните мне!',
                'modalStatusInit'=> 'Введите номер (10 цифр)',
                'modalStatusInputTip' => 'Введите все 10 цифр номера',
                'modalStatusInputOk' => 'С номером все ок, осталось нажать кнопку',
                'modalStatusRequest' => 'Запрос на звонок отправлен... ',
                'modalStatusCallMan' => 'Вызываем менеджера... ',
                'modalStatusZeroDef' => 'Ожидайте звонка... если его долго нет, попробуйте еще раз.',
                'error' => 'Ошибка!',
                'success' => 'Успешно!',
            ],
        ];
        $this->stdout('Setting defaults for variable models...' . PHP_EOL);
        foreach ($models as $class => $config) {
            $this->stdout($class . ': ');
            $class = '\\app\\models\\variable\\' . $class;
            /** @var \app\models\VariableModel $model */
            $model = new $class($config);
            if ($model->save()) {
                $this->stdout('ok!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('FAILED!' . PHP_EOL, Console::FG_RED);
            }
        }

        // Инициализация переменных по отдельности
        $defaults = [
        ];
        $this->stdout('Setting variables...' . PHP_EOL);
        foreach ($defaults as $name => $value) {
            $this->stdout($name .' => '. $value . ' ... ');
            if (Variable::sSet($name, $value)) {
                $this->stdout("ok\n", Console::FG_GREEN);
            } else {
                $this->stdout("FAILED\n", Console::FG_RED);
            }
        }

    }

}
