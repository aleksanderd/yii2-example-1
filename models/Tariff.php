<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\base\tplModel;

/**
 * Модель тарифа. БД таблица "{{%tariff}}".
 *
 * Тариф или тарифный план, задаётся админами.
 *
 * `title` - Заголовок тарифа.
 * `desc` - Короткое описание тарифа.
 * `desc_detail` - Детальное описание тарифа.
 * `desc_internal` - Внутреннее описание тарифа.
 *
 * `renewable` - Если >0, то тариф является автопроляемым.
 *
 * Основные ограничения:
 * `minutes` - Количество минуты разговора.
 * `messages` - Количество сообщений.
 * `queries` - Количество полезных(с разговором) запросов.
 * `space` - Объем дискового пространства.
 * `lifetime` - Срок действия тарифа.
 *
 * Если `lifetime` == 0, то тариф не имеет срока действия и будет рабочим пока не выработаются остальные лимиты.
 * Иначе, значение `lifetime` интерпретируется к количество дней или месяцев, в зависимости от значения `lifetime_measure`.
 *
 * @property integer $id Идентификатор тарифа
 * @property integer $status Статус тарифа
 * @property string $title Заголовок тарифа
 * @property string $desc Описание тарифа
 * @property string $desc_details Детальное описание тарифа
 * @property string $desc_internal Внутреннее описание тарифа (не показывается пользователям)
 * @property integer $renewable 0 - разовый тариф, 1 - продляемый
 * @property string $price Цена тарифа
 * @property integer $lifetime_measure Еденица измерения срока действия [[$lifetime]]. 1 - день, 30 - месяц
 * @property integer $lifetime Срок действия тарифа. В еденицах измерения из [[$lifetime_measure]]. 0 - не ограничено.
 * @property integer $queries Ограничение по количество обработанных запросов. 0 - не ограничено.
 * @property integer $minutes Ограничение по количеству минут разговора. 0 - не ограничено.
 * @property integer $messages Ограничение по количеству смс-сообщений. 0 - не ограничено.
 * @property integer $space Ограничение дискового пространства. 0 - не ограничено.
 * @property integer $created_at Время создания
 * @property integer $updated_at Время изменения
 *
 * @property UserTariff[] $userTariffs
 */
class Tariff extends \yii\db\ActiveRecord
{

    use tplModel;

    /** Статусы тарифа */
    const STATUS_INTERNAL = 0;
    const STATUS_PUBLIC = 100;

    /** Константы едениц измерения срока действия */
    const LTM_DAY = 1;
    const LTM_MONTH = 30;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tariff}}';
    }

    public static function statusLabels()
    {
        return [
            static::STATUS_PUBLIC => Yii::t('app', 'Available for all users'),
            static::STATUS_INTERNAL => Yii::t('app', 'Internal use only'),
        ];
    }

    public static function ltmLabels()
    {
        return [
            static::LTM_DAY => Yii::t('app', 'Day'),
            static::LTM_MONTH => Yii::t('app', 'Month'),
        ];
    }

    /**
     * @param Tariff|UserTariff $model
     * @return string
     */
    public static function getRenewableReadable($model)
    {
        return intval($model->renewable) > 0 ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
    }

    /**
     * @param Tariff|UserTariff $model
     * @return string
     */
    public static function getLifetimeReadable($model)
    {
        if (intval($model->lifetime) > 0) {
            if ($model->lifetime_measure == static::LTM_DAY) {
                return Yii::t('app', '{lt, plural, =1{# day} other{# days}}', ['lt' => $model->lifetime]);
            } else if ($model->lifetime_measure == static::LTM_MONTH) {
                return Yii::t('app', '{lt, plural, =1{# month} other{# months}}', ['lt' => $model->lifetime]);
            } else {
                return Yii::t('app', '{lt} U:{ltm}', ['lt' => $model->lifetime, 'ltm' => $model->lifetime_measure]);
            }
        } else {
            return Yii::t('app', 'unlimited');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'price'], 'required'],
            [['desc_details', 'desc_internal'], 'string'],
            [['status', 'renewable', 'lifetime_measure', 'lifetime', 'queries', 'minutes', 'messages', 'space'], 'integer'],
            [['price'], 'number'],
            [['title'], 'string', 'max' => 50],
            [['desc'], 'string', 'max' => 255],
            [['title'], 'unique'],

            [['lifetime', 'queries', 'minutes', 'messages', 'space'], 'default', 'value' => 0],
            ['renewable', 'default', 'value' => 0],
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
            'title' => Yii::t('app', 'Title'),
            'desc' => Yii::t('app', 'Short description'),
            'desc_details' => Yii::t('app', 'Detailed description'),
            'desc_internal' => Yii::t('app', 'Internal description'),
            'renewable' => Yii::t('app', 'Renewable'),
            'price' => Yii::t('app', 'Price'),
            'lifetime_measure' => Yii::t('app', 'Lifetime Measure'),
            'lifetime' => Yii::t('app', 'Lifetime'),
            'queries' => Yii::t('app', 'Queries'),
            'minutes' => Yii::t('app', 'Minutes'),
            'messages' => Yii::t('app', 'Messages'),
            'space' => Yii::t('app', 'Disk space'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTariffs()
    {
        return $this->hasMany(UserTariff::className(), ['tariff_id' => 'id']);
    }

    /**
     * Выполняет поиск тарифов которые могут быть продлены, и, если активных тарифов нет, продлевает их. Если, при этом
     * тариф требует оплаты (`status` < [[UserTariff::STATUS_READY]]), будет произведена оплата. Если продляемый тариф
     * уже активен, то он просто возвращается, без каких либо доп.действий.
     *
     * @param User|int $user
     * @param null|bool|ClientQuery $query Запрос, при котором идёт проверка-обновление
     * @return UserTariff|null
     */
    public static function userAutoRenew($user, $query = null)
    {
        /** @var User $user */
        if (is_int($user)) {
            $user = User::findOne($user);
        }
        // ищем продляемые и оплаченные тарифы
        /** @var UserTariff $model */
        $model = UserTariff::find()->where(['AND',
                ['user_id' => $user->id],
                ['renew' => 1],
                ['>', 'status', UserTariff::STATUS_DRAFT],
            ])->orderBy(['status' => SORT_DESC])->limit(1)->one();

        if (!($model)) {
            if ($query === null) {
                return null;
            }
            $activation = Variable::sGet('s.settings.trialActivation', $user->id);
            if ($activation < 90) {
                return null;
            }
            // ищем бесплатный тариф
            $model = UserTariff::find()->where([
                'user_id' => $user->id,
                'status' => UserTariff::STATUS_READY,
                'price' => 0,
            ])->limit(1)->one();

            if (!$model) {
                return null;
            }
        }

        if ($model->status >= UserTariff::STATUS_ACTIVE) {
            // Уже есть активный тариф
            return $model;
        }
        if ($model->status < UserTariff::STATUS_READY) {
            if (!($user->balance >= $model->price && $model->createTransaction()->save())) {
                // TODO здесь вставить пиналку юзера по СМС и мылу
                return null;
            }
        }
        $model->started_at = time();
        $model->status = UserTariff::STATUS_ACTIVE;
        if ($model->save(false, ['status', 'started_at'])) {
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Возвращает БД-запрос для получения активных тарифов для заданного пользователя.
     *
     * @param \app\models\User|int $user
     * @return \yii\db\ActiveQuery
     */
    public static function userGetActiveQuery($user)
    {
        /** @var \app\models\User $user */
        if (!($user instanceof User)) {
            if (!($user = User::findOne($user))) {
                return null;
            }
        }
//        $orderBy = '';
//        foreach (['seconds', 'messages', 'queries', 'space'] as $p) {
//            $orderBy .= sprintf('IF(`%s`>0, `%s`-`%s_used`, `%s_used`)', $p, $p, $p, $p);
//        }
        $orderBy = 'IF(`seconds`>0, NOW()+`seconds`-`seconds_used`, 2*NOW()+`seconds_used`)';
        $orderBy = 'IF(`lifetime`>0, `started_at`+86400*`lifetime`*`lifetime_measure`, '. $orderBy .') ASC';
        return UserTariff::find()
            ->where(['user_id' => $user->id, 'status' => UserTariff::STATUS_ACTIVE])
            ->andWhere(['<=', 'started_at', time()])
            ->orderBy($orderBy);
    }

    /**
     * Возвращает активный тариф для заданного пользователя.
     *
     * @param User|integer $user
     * @param bool $autoRenew
     * @param null|bool|ClientQuery $query Запрос, при котором идёт проверка-обновление
     * @return UserTariff|null
     */
    public static function userGetActive($user, $autoRenew = true, $query = null)
    {
        /** @var \app\models\User $user */
        if (!($user instanceof User)) {
            if (!($user = User::findOne($user))) {
                return null;
            }
        }
        /** @var UserTariff[] $active */
        $active = static::userGetActiveQuery($user)->all();
        foreach ($active as $k => $a) {
            if ($a->isEmpty) {
                $a->finish();
                unset($active[$k]);
            }
        }
        if (count($active) < 1) {
            return $autoRenew ? static::userAutoRenew($user, $query) : null;
        } else {
            return array_values($active)[0];
        }
    }
}
