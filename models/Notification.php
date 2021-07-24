<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%notification}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $page_id
 * @property integer $query_id
 * @property integer $type
 * @property integer $at
 * @property integer $status
 * @property string $from
 * @property string $to
 * @property string $subject
 * @property string $body
 * @property string $description
 *
 * @property-red string $title
 * @property ClientQuery $query
 * @property ClientSite $site
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{

    const STATUS_INIT = 0;
    const STATUS_DELAYED = 10;
    const STATUS_SEND_TRY = 99;
    const STATUS_SEND_SUCCESS = 100;
    const STATUS_SEND_FAIL = -100;

    const TYPE_EMAIL = 1;
    const TYPE_SMS = 2;

    public function getTitle()
    {
        if ($this->type == static::TYPE_SMS) {
            return $this->body;
        } else {
            return $this->subject;
        }
    }

    public static function typeLabels()
    {
        return [
            static::TYPE_EMAIL => Yii::t('app', 'SMTP'),
            static::TYPE_SMS => Yii::t('app', 'SMS'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_id', 'page_id', 'query_id', 'type', 'at', 'status'], 'integer'],
            [['body'], 'string'],
            [['from', 'to', 'subject', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'site_id' => Yii::t('app', 'Website ID'),
            'page_id' => Yii::t('app', 'Page ID'),
            'query_id' => Yii::t('app', 'Query ID'),
            'type' => Yii::t('app', 'Type'),
            'at' => Yii::t('app', 'At'),
            'status' => Yii::t('app', 'Status'),
            'from' => Yii::t('app', 'From'),
            'to' => Yii::t('app', 'To'),
            'subject' => Yii::t('app', 'Subject'),
            'body' => Yii::t('app', 'Body'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->hasOne(ClientQuery::className(), ['id' => 'query_id']);
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
     * Отправляет емейл и/или смс пользователю, в зависимости от типа нотификации.
     *
     * Пример импользования:
     *
     * ~~~php
     * $notify_mail = new Notification([
     *   'from' => 'sender@email.com',
     *   'to' => 'recipient@email.ru',
     *   'type' => Notification::NOTIFY_TYPE_EMAIL,
     *   'subject' => 'Subject text',
     *   'body' => 'Body text',
     * ]);
     * $notify->send();
     *
     * $notify_sms = new Notification([
     *   'from' => '+79512345678',
     *   'to' => '+79519876543',
     *   'type' => Notification::NOTIFY_TYPE_SMS,
     *   'subject' => 'Test '.time(),
     *   'body' => 'Body text',
     * ]);
     * $notify->send();
     * ~~~
     *
     * @param boolean $autoSave Сохранять или нет в базу
     * @param bool $delayed
     * @return string
     */
    public function send($autoSave = true, $delayed = true)
    {
        $cost = 0;
        $res = false;
        if ($delayed) {
            $this->status = static::STATUS_DELAYED;
            $this->description = Yii::t('app', 'Notification delayed.');
        } else if ($this->type == Notification::TYPE_SMS) {
            if ($res = $this->sendSms()) {
                $cost += Variable::sGet('s.price.sms', $this->user_id, $this->site_id, $this->page_id);
            }
        } else if ($this->type == Notification::TYPE_EMAIL) {
            if ($res = $this->sendMail()) {
                $cost += Variable::sGet('s.price.email', $this->user_id, $this->site_id, $this->page_id);
            }
        } else {
            $this->description = 'Unknown notify type';
        }
        if ($autoSave) {
            if ($this->save()) {
                if ($cost > 0) {
                    $transaction = new Transaction([
                        'user_id' => $this->user_id,
                        'notification_id' => $this->id,
                        'query_id' => $this->query_id,
                        'amount' => -1 * $cost,
                    ]);
                    $transaction->save();
                }
            } else {
                return false;
            }
        }
        return $res ? $this->description : $res;
    }

    /**
     * @return bool В случае успешной отправки - 'true`, иначе - `false`.
     */
    public function sendSmsClickatellSms()
    {
        $this->status = static::STATUS_SEND_TRY;
        try {
            $res = Yii::$app->sms->send([
                'to' => $this->to,
                'message' => $this->body
            ]);
        } catch (\Exception $e) {
            $this->status = static::STATUS_SEND_FAIL;
            $this->description = $e->getMessage();
            return false;
        }
        if (is_array($res)) {
            $this->status = static::STATUS_SEND_SUCCESS;
            $this->description = Yii::t('app', 'SMS sent') .'; '. implode(', ', $res);
            return true;
        } else {
            $this->status = static::STATUS_SEND_FAIL;
            $this->description = Yii::t('app', 'SMS send failed');
            return false;
        }
    }

    /**
     * @return bool В случае успешной отправки - 'true`, иначе - `false`.
     */
    public function sendSmsSmsc()
    {
        $this->status = static::STATUS_SEND_TRY;
        try {
            $res = Yii::$app->sms->send_sms($this->to, $this->body, 0, 0, 0, 0, "gmcf.ru");
        } catch (\Exception $e) {
            $this->description = $e->getMessage();
            return false;
        }
        if (Yii::$app->sms->isSuccess($res)) {
            $this->status = static::STATUS_SEND_SUCCESS;
            $this->description = Yii::t('app', 'SMS sent');
            return true;
        } else {
            $this->status = static::STATUS_SEND_FAIL;
            $this->description = Yii::t('app', 'SMS send failed: ' . $res[1]);
            return false;
        }
    }

    /**
     * Отправляет СМС. Проверяет класс компоненты, и в зависимости от этого вызывает
     * [[sendSmsClickatellSms()]] или [[sendSmsSmsc()]]
     *
     * @return bool В случае успешной отправки - 'true`, иначе - `false`.
     */
    public function sendSms()
    {
        // TODO: Добавить поле FROM в отправку
        // TODO: Сделать очиску сообщения перед отправкой
        $sms = Yii::$app->sms;
        if ($sms instanceof \snickom\clickatell\ClickatellSms) {
            return $this->sendSmsClickatellSms();
        } else if ($sms instanceof \ladamalina\smsc\Smsc) {
            return $this->sendSmsSmsc();
        } else {
            $this->status = static::STATUS_SEND_FAIL;
            $this->description = Yii::t('app', 'Unknown type of sms component');
            return false;
        }
    }

    /**
     * @return bool В случае успешной отправки - 'true`, иначе - `false`.
     */
    public function sendMail()
    {
        $this->status = static::STATUS_SEND_TRY;
        try {
            $to = explode(',', $this->to);
            $from = Variable::sGet('u.notify.emailFrom');
            $m = Yii::$app->mailer->compose()
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($this->subject)
                ->setTextBody($this->body);

            if ($from != $this->from) {
                $m->setReplyTo($this->from);
            }
            $res = $m->send();

        } catch (\Exception $e) {
            $eDescription = $e->getMessage();
            $res = false;
        }
        if ($res) {
            $this->status = static::STATUS_SEND_SUCCESS;
            $this->description = Yii::t('app', 'E-mail sent');
        } else {
            $this->status = static::STATUS_SEND_FAIL;
            $this->description = Yii::t('app', 'E-mail send failed');
            if (isset($eDescription) && strlen($eDescription) > 0) {
                $this->description .= ': ' . $eDescription;
            }
        }
        return $res;
    }

    /**
     * Создает объект Notification для отправки эл.письма.
     * Возможные параметры конфига:
     * `prefix` - Префикс переменных, для получения шаблонов EmailSubject & EmailBody
     * `user_id` - ID пользователя. Обязательно
     * `site_id` - ID сайта, если уместно. Опционально
     * `page_id` - ID страницы, если уместно. Опционально
     * `placeholders` - Массив для замены подставляемых строк в шаблоне.
     *
     * Кроме того, в конфиг можно передать любые свойства класса [[Notification]].
     *
     * @param array $config
     * @return Notification|null
     */
    public static function createVariableEmail($config)
    {
        /** @var User $user */
        $user = User::findOne(ArrayHelper::getValue($config, 'user_id'));
        $prefix = ArrayHelper::remove($config, 'prefix');
        if (!($user && $prefix)) {
            return null;
        }
        $placeholders = ArrayHelper::remove($config, 'placeholders', []);
        return new Notification(array_merge([
            'type' => Notification::TYPE_EMAIL,
            'from' => Variable::sGet('u.notify.emailFrom', $config),
            'to' => $user->getNotifyEmail(
                ArrayHelper::getValue($config, 'site_id'),
                ArrayHelper::getValue($config, 'page_id')
            ),
            'subject' => strtr(Variable::sGet($prefix . 'EmailSubject', $config), $placeholders),
            'body' => strtr(Variable::sGet($prefix . 'EmailBody', $config), $placeholders),
        ], $config));
    }

    /**
     * Создает объект Notification для отправки Sms.
     * Возможные параметры конфига аналогично [[Notification::createVariableEmail]].
     *
     * @param array $config
     * @return Notification|null
     */
    public static function createVariableSms($config)
    {
        /** @var User $user */
        $user = User::findOne(ArrayHelper::getValue($config, 'user_id'));
        $prefix = ArrayHelper::remove($config, 'prefix');
        if (!($user && $prefix)) {
            return null;
        }
        $to = $user->getNotifyPhone(
            ArrayHelper::getValue($config, 'site_id'),
            ArrayHelper::getValue($config, 'page_id')
        );
        if (strlen($to) > 10) {
            $placeholders = ArrayHelper::remove($config, 'placeholders', []);
            $body = strtr(Variable::sGet($prefix . 'SmsBody', $config), $placeholders);
            return new Notification(array_merge([
                'type' => Notification::TYPE_SMS,
                'to' => $to,
                'body' => $body,
            ], $config));
        }
        return null;
    }

    /**
     * Отправляет уведомления по заданным в $config настройкам. Обязательный параметр `prefix` используется для
     * определения имен переменных с шаблонами сообщения (например, `u.notify.query` или `u.notify.tariffEnd`).
     *
     * Остальные параметры конфига аналогично [[Notification::createVariableEmail]] плюс параметр `type`, соответсвующий
     * типу увеломления: 0 - не уведомлять, 1 - эл.почта, 2 - смс, 3 - и то, и то. Если `type` не задан, или строка,
     * то значение будет взято из переменной пользователя `prefix` + `type`.
     *
     * @param $config
     * @return bool
     */
    public static function sendVariableMessage($config)
    {
        if (!($prefix = ArrayHelper::getValue($config, 'prefix'))) {
            return false;
        }
        $type = ArrayHelper::remove($config, 'type', '');
        if (!is_int($type)) {
            $type = intval(Variable::sGet($prefix . $type, $config));
        }
        $result = true;
        if ($type & static::TYPE_EMAIL) {
            $n = static::createVariableEmail($config);
            $result = $n && $n->send() && $result;
            if ($prefix[0] == 'u' && ($copyTo = Variable::sGet('u.notify.copyTo', $config))) {
                $cfg = array_merge($config, ['to' => $copyTo]);
                if ($n = static::createVariableEmail($cfg)) {
                    $n->user_id = null;
                    $n->send();
                }
            }
        }
        if ($type & static::TYPE_SMS) {
            $n = static::createVariableSms($config);
            $result = $n && $n->send() && $result;
        }
        return $result;
    }

    /**
     * @param ClientQuery $model
     * @param null $event
     * @param null $type
     * @return bool
     */
    public static function onQuery(ClientQuery $model, $event = null, $type = null)
    {
        if (!isset($model->user_id) || $model->user_id < 1) {
            return false;
        }
        if (!isset($event)) {
            if ($model->status === ClientQuery::STATUS_UNPAID) {
                $event = 'queryUnpaid';
            } else if ($model->isSuccess) {
                $event = 'querySuccess';
            } else {
                $event = 'queryFail';
            }
        }
        if (!isset($type)) {
            $type = '';
        }
        return static::sendVariableMessage([
            'user_id' => $model->user_id,
            'site_id' => $model->site_id,
            'page_id' => $model->page_id,
            'query_id' => $model->id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ]);
    }

    public static function onTariff(UserTariff $model, $event = null, $type = null)
    {
        if (!isset($event)) {
            if ($model->status == UserTariff::STATUS_FINISHED) {
                $event = 'tariffEnd';
            } else if ($model->status == UserTariff::STATUS_RENEW) {
                $event = 'tariffRenewFail';
            }
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $model->user_id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        $result = static::sendVariableMessage($config);
        if ($event == 'tariffRenewFail') {
            unset($config['type']);
            if ($mail = static::createVariableEmail($config)) {
                $mail->to = Variable::sGet('s.settings.salesEmail');
                $mail->sendMail();
            }
        }
        return $result;
    }

    public static function onUser(User $model, $event = null, $type = null)
    {
        if (!isset($event)) {
            if ($model->balance < $model->minBalance) {
                $event = 'minBalance';
            } else {
                $event = 'userNew';
            }
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $model->id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        $result = static::sendVariableMessage($config);
        if ($event == 'minBalance') {
            unset($config['type']);
            if ($mail = static::createVariableEmail($config)) {
                $mail->to = Variable::sGet('s.settings.salesEmail');
                $mail->sendMail();
            }
        }
        $config['prefix'] = 's.notify.' . $event;
        $config['to'] = Variable::sGet('s.notify.emailTo', $model->id);
        return static::sendVariableMessage($config) && $result;
    }

    public static function onSite(ClientSite $model, $event = null, $type = null)
    {
        if (!isset($event)) {
            $event = 'siteNew';
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $model->user_id,
            'site_id' => $model->id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        $result = static::sendVariableMessage($config);
        $config['prefix'] = 's.notify.' . $event;
        $config['to'] = Variable::sGet('s.notify.emailTo', $model->user_id, $model->id);
        return static::sendVariableMessage($config) && $result;
    }

    public static function onPayment(Payment $model, $event = null, $type = null)
    {
        if (!isset($event)) {
            $event = 'paymentNew';
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $model->user_id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        $result = static::sendVariableMessage($config);
//        $config['prefix'] = 's.notify.' . $event;
//        $config['to'] = Variable::sGet('s.notify.emailTo', $model->user_id);
        $config['to'] = Variable::sGet('s.settings.salesEmail', $model->user_id);
        return static::sendVariableMessage($config) && $result;
    }

    public static function onPayout(Payout $model, $event = null, $type = null)
    {
        if (!isset($event)) {
            if ($model->status == Payout::STATUS_REQUEST) {
                $event = 'payoutRequest';
            } else {
                return false;
            }
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $model->user_id,
            'prefix' => 'u.notify.' . $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        $result = static::sendVariableMessage($config);
        $config['prefix'] = 's.notify.' . $event;
        $config['to'] = Variable::sGet('s.notify.emailTo', $model->user_id);
        return static::sendVariableMessage($config) && $result;
    }

    public static function onSMessage(SMessage $model, $event = null, $type = null)
    {
        if (!($ticket = $model->ticket)) {
            return false;
        }
        $isAuthor = $ticket->user_id == $model->user_id;
        if (!isset($event)) {
            $event = $isAuthor ? 'supportRequest' : 'supportReplied';
        }
        if (!isset($type)) {
            $type = '';
        }
        $config = [
            'user_id' => $ticket->user_id,
            'prefix' => ($isAuthor ? 's.notify' : 'u.notify') .'.'. $event,
            'type' => $type,
            'placeholders' => $model->tplPlaceholders(),
        ];
        if ($isAuthor) {
            $config['to'] = Variable::sGet('s.settings.supportEmail', $model->user_id);
        }
        return static::sendVariableMessage($config);
    }

}
