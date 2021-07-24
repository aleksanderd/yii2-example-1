<?php

namespace app\models;

use app\base\tplModel;
use app\helpers\DataHelper;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Класс ClientRule - модель правила.
 * Таблица в БД: {{%client_rule}}.
 *
 * @property integer $id Идентификатор правила
 * @property integer $user_id Идентификатор пользователя - владельца правила
 * @property integer|null $site_id Идентификатор сайта.
 * Если не установлено - правило используется для всех сайтов пользователя.
 * @property integer|null $page_id Идентификатор страницы.
 * Если не установлено - правило используется для всех страниц сайта.
 * @property integer $active Активное (>0) или нет правило.
 * @property integer $priority Приоритет.
 * Правила с наибольшим приоритетом проверяются в первую очередь.
 * @property integer $tm_week Маска(битовая) для фильтра по дням недели. Биты 0..6 означают день недели.
 * @property integer $tm_day Маска(битовая) для фильтра по часам суток. Биты 0..23 означают час в сутках.
 * @property string $title Заголовок/название правила
 * @property string $description Описание правила
 * @property string $timezone Временная зона
 * @property string $condition_data Дополнительные данные для условий
 * @property string $result_data Данный результата, такие как список линий, как звонить и тд.
 *
 * @property string $timezoneLabel
 * @property ClientSite|null $site Сайт для которого использовать это правило.
 * Если не определено, значит правило используется для всех сайтов пользователя - владельца правила
 * @property ClientPage|null $page Страница для которой использовать это правило.
 * Если не определено, значит правило используется для всех страниц сайта.
 * @property User $user Пользователь - владелец правила
 * @property ClientQuery[] $clientQueries Запросы на звонки, отработанные по этому правилу
 * @property ClientLine[] $lines Линии назначенные для правила
 *
 */
class ClientRule extends \yii\db\ActiveRecord
{

    use tplModel;

    public $condition = null;
    public $linesIDs;
    public $tmWeek;
    public $tmDay;

    /** @var array Массив чисел обозначающих рабочие часы, по принципу <день недели(0..6)>*24 + <час(0..23)>
     */
    public $hours;

    /** @var integer Для селектора одной линии */
    public $line_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_rule}}';
    }

    public static function activeLabels()
    {
        return [
            1 => Yii::t('app', 'Rule is active'),
            0 => Yii::t('app', 'Rule is not active (disabled)'),
        ];
    }

    public function getTimezoneLabel()
    {
        if (strlen($this->timezone) > 0) {
            return DataHelper::timezoneFull($this->timezone);
        } else {
            $timezone = Variable::sGet('u.settings.timezone', $this->user_id, $this->site_id, $this->page_id);
            return Yii::t('app', 'User timezone: {tz}', ['tz' => DataHelper::timezoneFull($timezone)]);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['linesIDs', 'tmWeek', 'tmDay', 'hours'], 'safe'],
            [['title', 'priority'], 'required'],
            [['user_id', 'active'], 'integer'],
            [['site_id', 'page_id', 'priority'], 'safe'],
            [['timezone', 'condition_data', 'result_data'], 'string'],
            [['title'], 'string', 'min' => 3, 'max' => 70],
            [['description'], 'string', 'max' => 255],
            [
                ['user_id', 'site_id', 'page_id', 'title'],
                'unique', 'targetAttribute' => ['user_id', 'site_id', 'page_id', 'title'],
                'message' => Yii::t('app', 'Rule with such title already exists.'),
            ],
//            ['line_id', 'integer'],
//            ['line_id', 'required'],

            [['linesIDs'], function ($attribute) {
                if (!(is_array($this->{$attribute}) && sizeof($this->{$attribute}) > 0)) {
                    $this->addError($attribute, Yii::t('app', 'Phone lines list can not be empty'));
                }
            }],

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
            'site_id' => Yii::t('app', 'Website'),
            'page_id' => Yii::t('app', 'Page'),
            'active' => Yii::t('app', 'Activation'),
            'priority' => Yii::t('app', 'Priority'),
            'title' => Yii::t('app', 'Title'),
            'timezone' => Yii::t('app', 'Timezone'),
            'description' => Yii::t('app', 'Description'),
            'tmWeek' => Yii::t('app', 'Work days'),
            'tmDay' => Yii::t('app', 'Work hours'),
            'condition_data' => Yii::t('app', 'Condition Data'),
            'result_data' => Yii::t('app', 'Result Data'),
            'lines' => Yii::t('app', 'Phone lines'),
            'linesIDs' => Yii::t('app', 'Phone lines'),
            'line_id' => Yii::t('app', 'Phone line'),
            'hours' => Yii::t('app', 'Rule hours'),
        ];
    }

    public function afterFind()
    {
        $this->tmWeek = DataHelper::bitsToArray($this->tm_week, 6);
        $this->tmDay = DataHelper::bitsToArray($this->tm_day, 23);
        $this->condition = unserialize($this->condition_data);
        $this->hours = ArrayHelper::getValue($this->condition, 'hours', []);
        parent::afterFind();
        $this->loadLinesIds();
        if (!isset($this->site_id)) {
            $this->site_id = 0;
        }
    }

    public function beforeSave($insert)
    {
        if ($this->site && ($this->site->user_id != $this->user_id) || $this->site_id < 1) {
            $this->site_id = null;
        }
        if (is_array($this->tmWeek)) {
            $this->tm_week = DataHelper::arrayToBits($this->tmWeek, 6);
        }
        if (is_array($this->tmDay)) {
            $this->tm_day = DataHelper::arrayToBits($this->tmDay, 23);
        }
        if (!is_array($this->condition)) {
            $this->condition = [];
        }
        $this->condition['hours'] = $this->hours;
        $this->condition_data = serialize($this->condition);
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->saveLinesIds();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientQueries()
    {
        return $this->hasMany(ClientQuery::className(), ['rule_id' => 'id']);
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
    public function getLines()
    {
        $query = ClientLine::find();
        $query->select('line.*')
            ->from('{{%client_line}} line')
            ->innerJoin('{{%client_rule_line}} rl', 'rl.line_id=line.id') // FIXME
            ->where(['rl.rule_id' => $this->id])
            ->orderBy(['priority' => SORT_DESC]);
        return $query;
/*

Этот вариант вроде как правилнее бы, но он НЕ сортирует по полю в промежуточной таблице :(
Ибо не юзает join а разделяет на два запроса - в первом сортирует, во втором уже нет.
        return $this->hasMany(ClientLine::className(), ['id' => 'line_id'])
            ->viaTable('{{%client_rule_line}}', ['rule_id' => 'id'], function(ActiveQuery $query) {
                return $query->orderBy('priority desc');
            });
*/
    }

    public function loadLinesIds()
    {
        $this->linesIDs = (new Query())
            ->select('line_id')
            ->from('{{%client_rule_line}}')
            ->where(['rule_id' => $this->id])
            ->orderBy(['priority' => SORT_DESC])
            ->column();

        if (is_array($this->linesIDs) && sizeof($this->linesIDs) > 0) {
            $this->line_id = $this->linesIDs[0];
        }
    }

    /**
     * Назначает (сохраняет в БД) линии для правила
     */
    public function saveLinesIds()
    {
        // очищаем назначенные линии
        ClientRuleLine::deleteAll(['rule_id' => $this->id]);

        // временно
        //$this->linesIDs = [$this->line_id];

        if (!is_array($this->linesIDs)) {
            return;
        }

        // получаем список линий с заданными id и принадлежащие пользоватлю
        // таким образом линии от другого пользователья назначить не получится.
        $allowedLines = (new Query())
            ->select('id')
            ->from('{{%client_line}}')
            ->where(['id' => $this->linesIDs, 'user_id' => $this->user_id])
            ->column();

        $priority = 1000;
        foreach ($this->linesIDs as $line_id) {
            if (!in_array($line_id, $allowedLines)) {
                continue;
            }
            (new ClientRuleLine([
                'rule_id' => $this->id,
                'line_id' => $line_id,
                'priority' => $priority--,
            ]))->save();
        }
    }

    /**
     * Проверяет клиентский запрос на соответсвие данному правилу.
     *
     * @param ClientQuery $query Клиентский запрос для проверки
     * @param bool|array $debug Массив для отладочной информации, false - если не надо
     * @return bool Результат проверки: true - правило подходит, false - не подходит
     */
    public function checkQuery(ClientQuery $query, &$debug = false)
    {
        if ($query->user_id < 1 || $this->user_id != $query->user_id) {
            return false;
        }
        if (($this->site_id > 0) && ($this->site_id != $query->site_id)) {
            if (is_array($debug)) {
                $debug['fail'] = sprintf(
                    'Rule.site_id[%d] != Query.site_id[%d]',
                    $this->site_id,
                    $query->site_id
                );
            }
            return false;
        }


        if (strlen($this->timezone) > 0) {
            $timezone = $this->timezone;
        } else if ($t = Variable::sGet('u.settings.timezone', $this->user_id, $this->site_id, $this->page_id)) {
            $timezone = $t;
        } else {
            $timezone = Yii::$app->timeZone;
        }
        $tz = new \DateTimeZone($timezone);
        $tzUTC = new \DateTimeZone('UTC');

        $dt = new \DateTime('@' . $query->at, $tzUTC);
        $dtStrUTC = $dt->format('Y-m-d H:i:s');
        $dt->setTimezone($tz);
        $dtStrTZ = $dt->format('Y-m-d H:i:s');
        $wday = intval($dt->format('w'));
        $hour = intval($dt->format('H'));
        $dayHour = $wday * 24 + $hour;

        if (is_array($debug)) {
            $debug['utc'] = $dtStrUTC;
            $debug['tz'] = $dtStrTZ;
            $debug['wday'] = $wday;
            $debug['hour'] = $hour;
            $debug['dayHour'] = $dayHour;
        }

        // TODO: можно сделать промежуточную таблицу rule_id -> hour
        if (!in_array($dayHour, $this->hours)) {
            if (is_array($debug)) {
                $debug['fail'] = sprintf('$dayHour[%d] not in allowed [%s]', $dayHour, implode(',', $this->hours));
            }
            return false;
        }

        if (is_array($debug)) {
            $debug['success'] = true;
        }
        return true;
    }

    public static function workTime($user_id, $site_id = null, $page_id = null)
    {
        $rules = ClientRule::find()->where(['AND',
            ['user_id' => $user_id],
            ['OR',
                ['site_id' => $site_id],
                ['site_id' => null],
            ],
            ['OR',
                ['page_id' => $page_id],
                ['page_id' => null],
            ],
        ])->orderBy([
            'user_id' => SORT_DESC,
            'site_id' => SORT_DESC,
            'page_id' => SORT_DESC,
            'priority' => SORT_DESC,
        ]);
//        $sql = $rules->createCommand()->rawSql;
//        echo $sql . PHP_EOL;
        $rules = $rules->all();
        /** @var ClientRule[] $rules */


        $result = [];
        $defTz = ($t = Variable::sGet('u.settings.timezone', $user_id, $site_id, $page_id)) ? $t : Yii::$app->timeZone;
        foreach ($rules as $r) {
            $tz = strlen($r->timezone) > 0 ? $r->timezone : $defTz;
            if (!isset($result['tz'])) {
                // временная зона из первого правила
                $result['tz'] = $tz;
            }
            if ($result['tz'] != $tz) {
                // пропускаем правила с другой временной зоной
                continue;
            }
            foreach ($r->hours as $wh) {
                $result[$wh % 24] = true;
            }
        }

        // для тестов
//        for ($i = 12; $i < 24; $i++) {
//            $result[$i] = true;
//        }

        ksort($result);
        return $result;
    }

}
