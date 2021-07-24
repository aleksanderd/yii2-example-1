<?php

namespace app\models;

use app\base\tplModel;
use app\models\query\UserQuery;
use dektrium\user\models\Token;
use Yii;
use dektrium\user\helpers\Password;


/**
 * Класс User - модель пользователя системы.
 * В качестве базы используется [сторонний модуль](https://github.com/dektrium/yii2-user).
 * Здесь же, определяются связи с другими моделями и тд.
 *
 * @property BlackCallInfo[] $blackCallInfos
 * @property ClientLine[] $clientLines
 * @property ClientPage[] $clientPages
 * @property ClientQuery[] $clientQueries
 * @property ClientQueryTest[] $clientQueryTests
 * @property ClientRule[] $clientRules
 * @property ClientSite[] $clientSites
 * @property ClientVisit[] $clientVisits
 * @property Conversion[] $conversions
 * @property Notification[] $notifications
 * @property Payment[] $payments
 * @property Payout[] $payouts
 * @property Profile $profile
 * @property Promocode[] $promocodes
 * @property PromocodeActivation[] $partnerPromocodeActivations
 * @property PromocodeActivation[] $promocodeActivations
 * @property ReferralStats[] $referralStats
 * @property ReferralUrl[] $referralUrls
 * @property Token[] $tokens
 * @property Transaction[] $transactions
 * @property UserReferral[] $referrals
 * @property UserReferral $partner
 * @property UserReferralTransaction[] $partnerTransactions
 * @property UserReferralTransaction[] $referralTransactions
 * @property UserTariff[] $userTariffs
 * @property Variable[] $variables
 * @property VariableValue[] $variableValues
 * @property User[] $subjectUsers
 *
 * @property string $http_referrer
 * @property-read string $notifyEmail
 * @property-read string $notifyPhone
 * @property float $balance
 * @property-read float $minBalance
 * @property-read bool $isPaid
 * @property-read float $partnerMaxPayout
 * @property-read boolean $isPayoutAllowed
 */
class User extends \dektrium\user\models\User {

    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    protected $_balance;
    protected $_minBalance;
    protected $_isPayoutAllowed;

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['notifyEmail', 'string'],
            ['http_referrer', 'string'],
        ]);
    }

    public function scenarios()
    {
        $res = parent::scenarios();
        $res['register'][] = 'http_referrer';
        return $res;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'http_referrer' => Yii::t('app', 'Url the user registered from'),
            'notifyEmail' => Yii::t('app', 'Notify email'),
            'balance' => Yii::t('app', 'Balance'),
        ]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $tid = Variable::sGet('s.settings.trialTariff');
            if ($tid && ($baseTariff = Tariff::findOne($tid))) {
                /** @var \app\models\Tariff $baseTariff */
                $tariff = new UserTariff([
                    'user_id' => $this->id,
                    'renew' => $baseTariff->renewable > 0 ? 1 : 0,
                ]);
                $tariff->applyTariff($baseTariff);
                if ($tariff->price > 0) {
                    $tariff->status = UserTariff::STATUS_DRAFT;
                } else {
                    $activation = Variable::sGet('s.settings.trialActivation', $this->id);
                    if ($activation > 99) {
                        $tariff->started_at = time();
                        $tariff->status = UserTariff::STATUS_ACTIVE;
                    } else {
                        $tariff->status = UserTariff::STATUS_READY;
                    }
                }
                $tariff->save();
            }

            Notification::onUser($this, 'userNew');
        }
    }

    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $this->confirmed_at = time();
        $this->password = $this->password == null ? Password::generate(8) : $this->password;

        $this->trigger(self::BEFORE_CREATE);

        if (!$this->save()) {
            return false;
        }

//        $this->mailer->sendWelcomeMessage($this, null, true);
        $this->trigger(self::AFTER_CREATE);

        return true;
    }

    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $this->confirmed_at = $this->module->enableConfirmation ? null : time();
        $this->password     = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

        $this->trigger(self::BEFORE_REGISTER);

        if (!$this->save()) {
            return false;
        }

        if ($this->module->enableConfirmation) {
            /** @var Token $token */
            $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
            $token->link('user', $this);
        }

//        $this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
        $this->trigger(self::AFTER_REGISTER);

        return true;
    }

    /**
     * Возвращаяет запрос к БД для получения линий ([[ClientLine]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getClientLines()
    {
        return $this->hasMany(ClientLine::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения запросов ([[ClientQuery]]) на связь для пользователя.
     * @return \yii\db\ActiveQuery
     */
    public function getClientQueries()
    {
        return $this->hasMany(ClientQuery::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения тестов ([[ClientQueryTest]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getClientQueryTests()
    {
        return $this->hasMany(ClientQueryTest::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения правил ([[Client`]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getClientRules()
    {
        return $this->hasMany(ClientRule::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения сайтов ([[ClientSite]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getClientSites()
    {
        return $this->hasMany(ClientSite::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientPages()
    {
        return $this->hasMany(ClientPage::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientVisits()
    {
        return $this->hasMany(ClientVisit::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения уведомлений ([[Notification]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения платежей ([[Payment]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocodes()
    {
        return $this->hasMany(Promocode::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTariffs()
    {
        return $this->hasMany(UserTariff::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения транзакций ([[Transaction]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения переменных ([[Variable]]), принадлежащих пользователю.
     * @return \yii\db\ActiveQuery
     */
    public function getVariables()
    {
        return $this->hasMany(Variable::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращаяет запрос к БД для получения значений переменных ([[VariableValue]]), принадлежащих пользователю
     * @return \yii\db\ActiveQuery
     */
    public function getVariableValues()
    {
        return $this->hasMany(VariableValue::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrals()
    {
        return $this->hasMany(UserReferral::className(), ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(UserReferral::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlackCallInfos()
    {
        return $this->hasMany(BlackCallInfo::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConversions()
    {
        return $this->hasMany(Conversion::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayouts()
    {
        return $this->hasMany(Payout::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerPromocodeActivations()
    {
        return $this->hasMany(PromocodeActivation::className(), ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocodeActivations()
    {
        return $this->hasMany(PromocodeActivation::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferralStats()
    {
        return $this->hasMany(ReferralStats::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferralUrls()
    {
        return $this->hasMany(ReferralUrl::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Token::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerTransactions()
    {
        return $this->hasMany(UserReferralTransaction::className(), ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferralTransactions()
    {
        return $this->hasMany(UserReferralTransaction::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращает пользователй которыми может управлять данный пользователь. Включая самого себя.
     * Для админа - все пользователи. Для простого пользователя - только его запись.
     * Для партнёров - свой запись и записи всех рефералов, которые разрешили управление.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectUsers()
    {
        $query = User::find()->orderBy(['username' => SORT_ASC]);
        $query->multiple = true;
        if ($this->isAdmin) {
            return $query;
        }
        $refIds = UserReferral::find()
            ->select('user_id')
            ->where(['partner_id' => $this->id, 'p_access' => UserReferral::ACCESS_ALLOW]);
        $query->andWhere(['OR',
            ['id' => $this->id],
            ['id' => $refIds],
        ]);
        return $query;
    }

    /**
     * Возвращает true если данному юзеру позволяется управлениние юзером с переданным id.
     *
     * @param integer $user_id
     * @return bool
     */
    public function checkSubject($user_id)
    {
        if ($this->id == $user_id || $this->isAdmin) {
            return true;
        }
        return $this->getSubjectUsers()->andWhere(['id' => $user_id])->exists();
    }

    /**
     * Возвращаяет текущий баланс пользователя.
     * @param bool $force
     * @return float
     */
    public function getBalance($force = false)
    {
        if ($force || $this->_balance === null) {
            $this->_balance = round($this->getTransactions()->sum('amount'), 2);
        }
        return $this->_balance;
    }

    public function setBalance($balance)
    {
        $this->_balance = $balance;
    }

    /**
     * Возвращает значение минимального баланса.
     * @param bool $force
     * @return float
     */
    public function getMinBalance($force = false)
    {
        if ($force || $this->_minBalance === null) {
            $result = Variable::sGet('u.notify.minBalanceValue', $this->id);
            $this->_minBalance = $result === null ? 0 : round($result, 2);
        }
        return $this->_minBalance;
    }

    /**
     * Возвращает `true` если баланс больше нуля или есть готовый(оплаченный) или активный тариф.
     * @return bool
     */
    public function getIsPaid()
    {
        if ($this->balance > 0) {
            return true;
        }
        $activeTariffs = $this->getUserTariffs()->andWhere(['>=', 'status', UserTariff::STATUS_READY]);
        return $activeTariffs->count() > 0;
    }

    /**
     * Возвращает максимально допустимую сумму для платежа партнёру.
     *
     * @return float
     */
    public function getPartnerMaxPayout()
    {
        //return 333;
        $earned = $this->getReferrals()->sum('paid');
        $paid = $this->getPayouts()->andWhere(['status' => Payout::STATUS_COMPLETE])->sum('amount');
        $result = floatval($earned) - floatval($paid);
        if ($this->balance < $result) {
            $result = $this->balance;
        }
        if (($payoutMax = Variable::sGet('s.settings.payoutMax', $this->id)) && $payoutMax < $result) {
            $result = $payoutMax;
        }
        return $result > 0 ? $result : 0;
    }

    public function getIsPayoutAllowed()
    {
        if (!isset($this->_isPayoutAllowed)) {
            $this->_isPayoutAllowed = $this->checkPayoutAmount()
                && $this->checkPayoutInterval()
                && $this->checkPayoutCount();
        }
        return $this->_isPayoutAllowed;
    }

    /**
     * Проверяет накопилось ли минималька.
     * @return bool
     */
    public function checkPayoutAmount()
    {
        return $this->partnerMaxPayout >= Variable::sGet('s.settings.payoutMin', $this->id);
    }

    /**
     * Проверяет вышел ли минимальный срок со времены последней выплаты.
     * @return bool
     */
    public function checkPayoutInterval()
    {
        return !$this->getPayouts()->where([
            'AND',
            ['status' => Payout::STATUS_COMPLETE],
            ['>', 'updated_at', time() - 86400 * Variable::sGet('s.settings.payoutInterval', $this->id)],
        ])->exists();
    }

    /**
     * Проверяет количество уже сужествующих запросов на выплаты.
     * @return bool
     */
    public function checkPayoutCount()
    {
        return $this->getPayouts()->where(['<=', 'status', Payout::STATUS_IN_PROCESS])->count() < 1;
    }

    /**
     * Возвращает эл.адрес пользователя, проверяя настройки уведомлений, профиля, и, если нигде адрес не указан,
     * возвращает адрес, указанный при регистрации.
     *
     * @param int|null $site_id
     * @param int|null $page_id
     * @return string
     */
    public function getNotifyEmail($site_id = null, $page_id = null)
    {
        $var = Variable::sGetModel('u.notify.emailTo', $this->id, $site_id, $page_id);
        if ($var && $var->user_id !== null && $var->user_id > 0) {
            $result = $var->value;
        } else {
            $result = $this->profile->public_email;
        }
        return strlen($result) > 5 ? $result : $this->email;
    }

    /**
     * Возвращает номер телефона пользователя, проверяя настройки уведомлений, если таковых нет, то возвращает значение
     * из профиля.
     *
     * @param int|null $site_id
     * @param int|null $page_id
     * @return string
     */
    public function getNotifyPhone($site_id = null, $page_id = null)
    {
        $var = Variable::sGetModel('u.notify.smsTo', $this->id, $site_id, $page_id);
        if ($var && $var->user_id !== null && $var->user_id > 0) {
            return $var->value;
        } else {
            return $this->profile->phone;
        }

    }

    /**
     * Возвращает путь до папки для загрузки файлов (относительно).
     * @return bool|string
     */
    public function getFilesPath()
    {
        return sprintf('%05d', $this->id);
    }

    /**
     * Возвращает массив строк для замены в шаблонах.
     *
     * Поля непосредственно из таблицы БД:
     * * {id}
     * * {username}
     * * {email}
     * ...другие больше системные.
     *
     * Другие поля:
     * * {timezone} - Временная зона из настроек пользователя.
     * * {datetime} - Строковое выражение текущего времени в выбранной временной зоне.
     * * {datetime.utc} - Строковое выражение текущего времени в зоне UTC.
     * * {balance} - Текущий баланс пользователя.
     * * {minBalance} - Минимальный баланс для уведомления из настроек.
     *
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);
        $tz = Variable::sGet('u.settings.timezone', $this->id);
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        $result['{'.$prefix.'balance}'] = sprintf('%.02f', $this->balance);
        $result['{'.$prefix.'minBalance}'] = sprintf('%.02f', $this->minBalance);
        $result['{'.$prefix.'password}'] = isset($this->password) ? $this->password : '********';
//        if ($partner = $this->partner) {
//            $result = array_merge($result, $partner->tplPlaceholders($prefix . 'partner.'));
//        }
        return $result;
    }

}
