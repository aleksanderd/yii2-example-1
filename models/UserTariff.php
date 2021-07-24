<?php

namespace app\models;

use app\helpers\DataHelper;
use Yii;
use app\base\tplModel;

/**
 * Модель, описывающая конкретное использование тарифа пользователем. БД таблица "{{%user_tariff}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $tariff_id
 * @property string $title
 * @property integer $status
 * @property integer $renew
 * @property integer $started_at
 * @property integer $finished_at
 * @property integer $renewable
 * @property integer $price
 * @property integer $lifetime_measure
 * @property integer $lifetime
 * @property integer $queries
 * @property integer $queries_used
 * @property integer $seconds
 * @property integer $seconds_used
 * @property integer $messages
 * @property integer $messages_used
 * @property integer $space
 * @property integer $space_used
 *
 * @property bool $isArchived
 * @property bool $isPaid
 * @property bool $isEmpty
 * @property integer $minutes
 * @property integer $lifetimeEnd
 * @property Tariff $tariff
 * @property Transaction $transaction
 * @property User $user
 */
class UserTariff extends \yii\db\ActiveRecord
{

    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    /** Черновик тарифа, не оплаченный, может быть удалён. */
    const STATUS_DRAFT = 0;
    /** Неоплаченный тариф после неуспешной попытки авто-продления. */
    const STATUS_RENEW = 10;
    /** Оплаченный и готовый к использованию тариф. */
    const STATUS_READY = 100;
    /** Активный тариф. */
    const STATUS_ACTIVE = 1000;
    /** Завершённый тариф. */
    const STATUS_FINISHED = -1000;

    public $_lifetimeEnd = false;

    public static function statusLabels()
    {
        return [
            static::STATUS_DRAFT => Yii::t('app', 'Unpaid: draft'),
            static::STATUS_RENEW => Yii::t('app', 'Unpaid: Auto renew'),
            static::STATUS_READY => Yii::t('app', 'Paid and ready'),
            static::STATUS_ACTIVE => Yii::t('app', 'Active'),
            static::STATUS_FINISHED => Yii::t('app', 'Finished'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_tariff}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title'], 'required'],
            [['user_id', 'tariff_id', 'status', 'renew', 'started_at', 'finished_at', 'lifetime_measure', 'lifetime', 'queries', 'seconds', 'messages', 'space'], 'integer'],
            [['price'], 'number'],
            [['title'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'tariff_id' => Yii::t('app', 'Tariff'),
            'title' => Yii::t('app', 'Title'),
            'status' => Yii::t('app', 'Status'),
            'renew' => Yii::t('app', 'Auto renew'),
            'started_at' => Yii::t('app', 'Started at'),
            'finished_at' => Yii::t('app', 'Finished at'),
            'lifetime_end' => Yii::t('app', 'End at'),
            'renewable' => Yii::t('app', 'Renewable'),
            'price' => Yii::t('app', 'Price'),
            'lifetime_measure' => Yii::t('app', 'Lifetime Measure'),
            'lifetime' => Yii::t('app', 'Lifetime'),
            'lifetimeEnd' => Yii::t('app', 'Lifetime end'),
            'queries' => Yii::t('app', 'Queries'),
            'queries_used' => Yii::t('app', 'Queries used'),
            'seconds' => Yii::t('app', 'Seconds'),
            'seconds_used' => Yii::t('app', 'Seconds used'),
            'messages' => Yii::t('app', 'Messages'),
            'messages_used' => Yii::t('app', 'Messages used'),
            'space' => Yii::t('app', 'Space'),
            'space_used' => Yii::t('app', 'Space used'),
        ];
    }

    public function __get($name)
    {
        if ($name == 'lifetimeEnd' && !isset($this->lifetimeEnd)) {
            $this->lifetimeEnd = $this->getLifetimeEnd();
        }
        return parent::__get($name);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(Transaction::className(), ['user_tariff_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Применяет значения настроек заданного тарифа. Параметром может быть как [[Tariff]] так и [[UserTariff]].
     *
     * @param Tariff|UserTariff $tariff
     * @return bool
     */
    public function applyTariff($tariff)
    {
        if (!isset($tariff)) {
            return false;
        }
        if ($tariff instanceof Tariff) {
            /** @var \app\models\Tariff $tariff */
            $this->tariff_id = $tariff->id;
        } else if ($tariff instanceof UserTariff) {
            /** @var \app\models\UserTariff $tariff */
            $this->tariff_id = $tariff->tariff_id;
            $this->renew = $tariff->renew;
        } else {
            return false;
        }
        foreach (['title', 'renewable', 'price', 'lifetime', 'lifetime_measure', 'minutes', 'queries', 'messages', 'space'] as $p) {
            $this->{$p} = $tariff->{$p};
        }
//        $this->seconds = $tariff->minutes * 60;
        if ($this->renewable == 0) {
            $this->renew = 0;
        }
        return true;
    }

    /**
     * Создает и возвращает транзакцию, для оплаты тарифа.
     *
     * @return Transaction
     */
    public function createTransaction()
    {
        return new Transaction([
            'user_id' => $this->user_id,
            'user_tariff_id' => $this->id,
            'amount' => -1 * $this->price,
            'description' => Yii::t('app', 'Paying of tariff "{tariff}"', ['tariff' => $this->title]),
        ]);
    }

    /**
     * Устанавливает количество миинут, преобразуя их в секунды и сохраняя в поле `seconds`.
     *
     * @param int $minutes
     */
    public function setMinutes($minutes)
    {
        $this->seconds = $minutes * 60;
    }

    /**
     * Возвращает количество минут в тарифе, переводя минуты значение поля `seconds`.
     *
     * @return float
     */
    public function getMinutes()
    {
        return floor($this->seconds / 60);
    }

    public function setLifetimeEnd($value)
    {
        $this->_lifetimeEnd = $value;
    }

    /**
     * Возвращает время предполагаемого окончания тарифа. Высчитывается как время старта `started_at` плюс
     * время жизни `lifetime`. Если `lifetime` = 0 (тариф не ограничен по времени), то возвращает дату "из далёкого будущего".
     *
     * @return int
     */
    public function getLifetimeEnd()
    {
        if ($this->_lifetimeEnd === false) {
            if ($this->lifetime > 0) {
                if ($this->lifetime_measure == Tariff::LTM_MONTH) {
                    $dt = new \DateTime();
                    $dt->setTimestamp(intval($this->started_at));
                    $dt->add(new \DateInterval(sprintf('P%dM', $this->lifetime)));
                    $this->_lifetimeEnd =$dt->getTimestamp();
                } else {
                    $this->_lifetimeEnd =$this->started_at + $this->lifetime * 86400;
                }
            } else {
                $this->_lifetimeEnd = $this->started_at * 33;
            }
        }
        return $this->_lifetimeEnd;
    }

    /**
     * Возвращает true, если тариф архивный (`status` < 0).
     *
     * @return bool
     */
    public function getIsArchived()
    {
        return $this->status < 0;
    }

    /**
     * Возвращает true, если тариф оплачен (`status` >= [[STATUS_READY]])
     *
     * @return bool
     */
    public function getIsPaid()
    {
        return $this->status >= static::STATUS_READY;
    }

    /**
     * Возвращает true, если тариф израсходован (вышел срок, кончились минуты и тд).
     *
     * @return bool
     */
    public function getIsEmpty()
    {
        if ($this->lifetime > 0 && $this->lifetimeEnd < time()) {
            return true;
        }
        $props = ['seconds', 'messages', 'queries', 'space'];
        foreach ($props as $p) {
            $limit = intval($this->{$p});
            $limit_used = intval($this->{$p . '_used'});
            if ($limit === 0) {
                // неограниченный ресурс
                continue;
            }
            if ($limit_used >= $limit) {
                return true;
            }
        }
        return false;
    }

    /**
     * Завершает пользовательский активный тариф. Если автопродление включено, будет создан аналогичный тариф, и
     * произведена оплата, если таковая требуется. В случае ошибки оплаты(например, нет денег), клонированный тариф
     * останется в списке неоплаченных, со статусом [[UserTariff::STATUS_RENEW]].
     *
     * @return bool
     */
    public function finish()
    {
        if ($this->status < static::STATUS_ACTIVE) {
            return false;
        }
        $time = time();
        $this->finished_at = $time;
        if ($this->lifetime && $this->lifetimeEnd < $this->finished_at) {
            $this->finished_at = $this->lifetimeEnd;
        }
        $this->status = static::STATUS_FINISHED;
        if (!$this->save(false, ['status', 'finished_at'])) {
            return false;
        }
        Notification::onTariff($this, 'tariffEnd');
        if ($this->renew) {
            // если включено автопродление, добавляем новую запись
            $model = new UserTariff([
                'user_id' => $this->user_id,
            ]);
            if ($tariff = $this->tariff) {
                $model->applyTariff($tariff);
            } else {
                $model->applyTariff($this);
            }
            $model->renew = $this->renew;
            if ($this->price > 0) {
                // Платный тариф - сначала платим
                $model->status = static::STATUS_RENEW;
                $model->save();
                if ($model->user->balance >= $model->price && $model->createTransaction()->save()) {
                    $model->status = static::STATUS_ACTIVE;
                    $model->started_at = $this->finished_at;
                    if ($model->lifetimeEnd < $time) {
                        $model->started_at = $time;
                    }
                    $model->save(false, ['status', 'started_at']);
                } else {
                    Notification::onTariff($model, 'tariffRenewFail');
                }
            } else {
                // Бесплатный тариф
                $model->status = static::STATUS_ACTIVE;
                $model->started_at = $this->finished_at;
                $model->save();
            }
        }
        return true;
    }

    /**
     * Возвращает массив строк для замены в шаблонах.
     *
     * Поля непосредственно из таблицы БД:
     *
     * * {id}
     * * {user_id}
     * * {tariff_id}
     * * {title} - Заголовок.
     * * {status} - Целочисленное!
     * * {renew} - Автопродление включено(1) или нет(0).
     * * {started_at} - Целочисленное! Unix-таймстамп старта тарифа.
     * * {finished_at} - Целочисленное! Unix-таймстамп завершения тарифа.
     * * {renewable} - Продляемый тариф(1) или нет(0).
     * * {price} - Стоимость.
     * * {lifetime_measure} - Единица измерения `lifetime`, 1 - дни, 30 - месяцы.
     * * {lifetime} - Срок действия тарифа. Целочисленное! В ед.измерения из `lifetime_measure`.
     * * {queries}
     * * {queries_used}
     * * {seconds}
     * * {seconds_used}
     * * {messages}
     * * {messages_used}
     *
     * Другие поля:
     * * {timezone} - Временная зона из настроек пользователя.
     * * {datetime} - Строковое выражение текущего времени в выбранной временной зоне.
     * * {datetime.utc} - Строковое выражение текущего времени в зоне UTC.
     * * {lifetimeText} - Срок действия тарифа строкой. Например, "10 days" или "1 month".
     * * {startedDatetime} - Строковое выражение времени старта тарифа (в выбранной временной зоне).
     * * {startedDatetime.utc} - Строковое выражение времени старта тарифа (в UTC).
     * * {finishedDatetime} - Строковое выражение времени завершения тарифа (в выбранной временной зоне).
     * * {finishedDatetime.utc} - Строковое выражение времени завершения тарифа (в UTC).
     * * {minutes} - Минуты тарифа в формате 00'00''.
     * * {minutes_used} - Использовано минут в формате 00'00''.
     *
     * Кроме того, можно использовать поля из модели [[User]], обращаясь к ним через 'user.': Например:
     * {user.username}, {user.email}, {user.balance} и тд. см. [[User::tplPlaceholders()]]
     *
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);
        $tz = Variable::sGet('u.settings.timezone', $this->user_id);
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        if ($user = $this->user) {
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }
        if ($tariff = $this->tariff) {
            $result = array_merge($result, $tariff->tplPlaceholders($prefix . 'tariff.'));
        }
        $result['{'.$prefix.'minutes}'] = DataHelper::durationToText($this->seconds);
        $result['{'.$prefix.'minutes_used}'] = DataHelper::durationToText($this->seconds_used);
        $result['{'.$prefix.'lifetimeText}'] = Tariff::getLifetimeReadable($this);

        $result = array_merge($result, tplModel::tplDatetimePlaceholders($this->started_at, $tz, $prefix . 'startedDatetime'));
        $result = array_merge($result, tplModel::tplDatetimePlaceholders($this->finished_at, $tz, $prefix . 'finishedDatetime'));
        return $result;
    }

}
