<?php

namespace app\commands;

use app\helpers\DataHelper;
use app\models\BlackCallInfo;
use app\models\ClientPage;
use app\models\ClientQuery;
use app\models\ClientRule;
use app\models\ClientSite;
use app\models\Notification;
use app\models\Payment;
use app\models\Payout;
use app\models\ReferralStats;
use app\models\SMessage;
use app\models\STicket;
use app\models\User;
use app\models\variable\WOptions;
use Yii;
use yii\console\Controller;
use app\models\Variable;
use yii\helpers\Console;
use yii\helpers\VarDumper;

class TestController extends Controller
{

    public function actionRuleHours()
    {
        $rules = ClientRule::find()->all();
        foreach ($rules as $rule) {
            /** @var ClientRule $rule */
            $rule->hours = [];
            foreach ($rule->tmWeek as $weekDay) {
                foreach ($rule->tmDay as $dayHour) {
                    $rule->hours[] = sprintf('%d', $weekDay * 24 + $dayHour);
                }
            }
            //print_r($rule->hours);
            $rule->save();
        }
    }

    public function setVariable($name, $value, $user_id = null, $site_id = null, $page_id = null)
    {
        $this->stdout(sprintf('Setting variable "%s" to value "%s": ', $name, $value));
        $res = Variable::sSet($name, $value, $user_id, $site_id, $page_id);
        if ($res === true) {
            $this->stdout('ok!' . PHP_EOL, Console::FG_GREEN);
            return true;
        } else {
            VarDumper::dump($res);
            return false;
        }
    }

    public function actionQuery()
    {
        //$black = BlackCallInfo::findOne(['OR', 'user_id' => 1, 'user_id' => null]);
        $black =BlackCallInfo::find()->where([
            'AND',
            ['call_info' => '123'],
            [
                'OR',
                ['user_id' => 1],
                ['user_id' => null]
            ],
        ]);
        echo $black->createCommand()->rawSql . PHP_EOL;

    }

    public function actionPageOld()
    {
        /** @var \app\models\ClientSite $site */
        $site = ClientSite::findOne(1);
        $urls = [
            'http://domain1.com/path/file/?p=v',
            'http://supersite.com/other-path/file2/?p2=v2',
        ];
        foreach ($urls as $url) {
            $site->findPageByUrl($url);
        }

    }

    public function actionPage($id, $url)
    {
        /** @var \app\models\ClientPage $page */
        if (!($page = ClientPage::findOne($id))) {
            echo 'Page not found' . PHP_EOL;
            return;
        }
        echo $page->testUrl($url) . PHP_EOL;
    }

    public function actionNotifyUser($id = 1, $event = 'userNew')
    {
        if ($model = User::findOne($id)) {
            /** @var \app\models\User $model */
            $this->stdout('Found user: ' . $model->username . PHP_EOL . 'Sending notification...');
            if (Notification::onUser($model, $event)) {
                $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed :(' . PHP_EOL, Console::FG_RED);
            }
        } else {
            $this->stdout('User not found :(' . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionNotifySite($id = 1, $event = 'siteNew')
    {
        if ($model = ClientSite::findOne($id)) {
            /** @var \app\models\ClientSite $model */
            $this->stdout('Found website: ' . $model->url . PHP_EOL . 'Sending notification...');
            if (Notification::onSite($model, $event)) {
                $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed :(' . PHP_EOL, Console::FG_RED);
            }
        } else {
            $this->stdout('Site not found :(' . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionNotifyQuery($id = 1, $event = null)
    {
        if ($model = ClientQuery::findOne($id)) {
            /** @var \app\models\ClientQuery $model */
            $this->stdout('Found query: ' . $model->id . PHP_EOL . 'Sending notification...');
            if (Notification::onQuery($model, $event)) {
                $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed :(' . PHP_EOL, Console::FG_RED);
            }
        } else {
            $this->stdout('Query not found :(' . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionNotifyPayment($id = 1, $event = null)
    {
        if ($model = Payment::findOne($id)) {
            /** @var \app\models\Payment $model */
            //print_r($model->tplPlaceholders()); exit;
            $this->stdout('Found payment: ' . $model->id . PHP_EOL . 'Sending notification...');
            if (Notification::onPayment($model, $event)) {
                $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed :(' . PHP_EOL, Console::FG_RED);
            }
        } else {
            $this->stdout('Payment not found :(' . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionNotifyPayout($id = 1, $event = null)
    {
        if ($model = Payout::findOne($id)) {
            /** @var \app\models\Payout $model */
            //print_r($model->tplPlaceholders()); exit;
            $this->stdout('Found payout: ' . $model->id . PHP_EOL . 'Sending notification...');
            if (Notification::onPayout($model, $event)) {
                $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed :(' . PHP_EOL, Console::FG_RED);
            }
        } else {
            $this->stdout('Payout not found :(' . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionNotify()
    {

        /** @var \app\models\SMessage $msg */
        $msg = SMessage::findOne(35);
        print_r($msg->tplPlaceholders());
        Notification::onSMessage($msg);

        return;
        /** @var \app\models\STicket $ticket */
        $ticket = STicket::findOne(3);
        print_r($ticket->tplPlaceholders());

        return;
//
//        /** @var \app\models\ClientQuery $query */
//        $query = ClientQuery::findOne(123);
////        Notification::onQuery($query);
//        return;

        /** @var \app\models\UserTariff $tariff */
//        $tariff = UserTariff::findOne(7);
//        Notification::onTariff($tariff);
    }

    public function actionMaillist()
    {
        echo '222';
        /** @var \app\components\MailListSendPulse $ml */
        $ml = \Yii::$app->maillist;

        /** @var \app\models\User $user */
        $user = User::findOne(1);

        $ml->removeEmails('all', $user->email);
    }

    public function actionMail($to, $from = 'noreply@gmcf.ru', $text = 'test msg from gmcf')
    {
        $res = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($text)
            ->setTextBody($text)
            ->send();
        print_r($res);
        echo PHP_EOL;
    }

    public function actionRefTest()
    {
        $res = ReferralStats::addValues([
            'user_id' => '2',
            'url_id' => '1',
            'datetime' => time(),

            'visits' => 11,
            'registered' => 3,
            'active' => 2,
            'paid' => 7.15,
        ]);
        if ($res) {
            $this->stdout('Values added!', Console::FG_GREEN);
        } else {
            $this->stdout('fail!', Console::FG_RED);
        }
        echo PHP_EOL;
    }

    public function actionVars()
    {
        $wo = new WOptions([
            'user_id' => 19,
        //    '_autoload' => false,
//            'language' => 'RU',
//            'buttonOptions' => [
//                'radius' => 21,
//            ],
        ]);

        echo $wo->dump();
    }

    public function actionWorkTime($user_id, $site_id = null, $page_id = null)
    {
        $res = ClientRule::workTime($user_id, $site_id, $page_id);
        print_r($res);
        echo PHP_EOL;
    }

    public function actionIdn($url = 'test.ru')
    {
        echo $url . PHP_EOL;
        echo 'idn: ' . DataHelper::idnUrl($url) . PHP_EOL;
        echo 'normal: ' . DataHelper::normalizeUrl($url) . PHP_EOL;
    }

}
