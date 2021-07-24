<?php

namespace app\models;

use app\base\tplModel;
use app\helpers\DataHelper;
use app\models\query\ClientSiteQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Класс ClientSite - модель сайта клиента, с которого предполагается совершать звонки.
 * Таблица в БД: {{%client_site}}
 *
 * @property integer $id Идентификатор сайта
 * @property integer $user_id Идентификатор пользователя - владельца сайта
 * @property string $title Заголовок сайта
 * @property string $description Описание сайта
 * @property string $url Адрес сайта
 * @property string $domain Домен(без www.) сайта
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $w_checked_at
 * @property integer $w_changed_at
 * @property integer $w_check_result
 *
 * @property ClientPage[] $clientPages
 * @property ClientQuery[] $clientQueries Клиентские запросы на звонки по сайту
 * @property ClientQueryTest[] $clientQueryTests Тесты запросов в которых указан этот сайт
 * @property ClientRule[] $clientRules Правила в которых назначен этот сайт
 * @property User $user Пользователь - владелец сайта
 * @property string $widgetCode Код виджета
 */
class ClientSite extends \yii\db\ActiveRecord
{

    const CODE_ERROR = -100;
    const CODE_NONE = 0;
    const CODE_OK = 100;

    public $defaultPrefix;

    use tplModel {
        tplPlaceholders as base_tplPlaceholders;
    }

    public static function wCodeResultStatuses()
    {
        return [
            static::CODE_ERROR => Yii::t('app', 'Error'),
            static::CODE_NONE => Yii::t('app', 'NO'),
            static::CODE_OK => Yii::t('app', 'OK'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_site}}';
    }

    /**
     * @return ClientSiteQuery
     */
    public static function find()
    {
        return new ClientSiteQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'url'], 'required'],
            [['user_id'], 'integer'],
            [['title'], 'string', 'min' => 3, 'max' => 70],
//            [['url'], 'string', 'min' => 10, 'max' => 255],
            ['url', 'url', 'defaultScheme' => 'http', 'enableIDN' => true],
            ['url', 'validateUrl'],
            [['description'], 'string', 'max' => 255],
            [['defaultPrefix'], 'string', 'max' => 5],
            [
                ['user_id', 'title'],
                'unique', 'targetAttribute' => ['user_id', 'title'],
                'message' => Yii::t('app', 'Website with such title already exists'),
            ],
            [
                ['user_id', 'url'],
                'unique', 'targetAttribute' => ['user_id', 'url'],
                'message' => Yii::t('app', 'Website with such url already exists'),
            ]
        ];
    }

    /**
     * Проверка уникальности домена.
     */
    public function validateUrl()
    {
        if (!$this->hasErrors()) {
            $this->url = DataHelper::normalizeUrl($this->url);
            $domain = DataHelper::getDomain($this->url);
            $query = ClientSite::find()->where(compact('domain'));
            if (!$this->isNewRecord) {
                $query->andWhere(['<>', 'id', $this->id]);
            }
            if ($query->exists()) {
                $this->addError('url', Yii::t('app', 'Website with such domain already exists.'));
            } else {
                $this->domain = $domain;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'title' => Yii::t('app', 'Website title'),
            'description' => Yii::t('app', 'Website description'),
            'url' => Yii::t('app', 'Website URL'),
            'domain' => Yii::t('app', 'Website domain'),
            'defaultPrefix' => Yii::t('app', 'Default prefix'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
            'w_checked_at' => Yii::t('app', 'Code Checked At'),
            'w_changed_at' => Yii::t('app', 'Code Changed At'),
            'w_check_result' => Yii::t('app', 'Code Check Result'),
        ];
    }

    public function afterFind()
    {
        $this->defaultPrefix = Variable::sGet('w.options.defaultPrefix', $this->user_id, $this->id);
        parent::afterFind();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            Notification::onSite($this, 'siteNew');
        }
        if (isset($this->defaultPrefix)) {
            Variable::sSet('w.options.defaultPrefix', $this->defaultPrefix, $this->user_id, $this->id);
        }
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientPages()
    {
        return $this->hasMany(ClientPage::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientQueries()
    {
        return $this->hasMany(ClientQuery::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientQueryTests()
    {
        return $this->hasMany(ClientQueryTest::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientRules()
    {
        return $this->hasMany(ClientRule::className(), ['site_id' => 'id']);
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
    public function getNotifications()
    {
        return $this->hasMany(Notification::className(), ['site_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariables()
    {
        return $this->hasMany(Variable::className(), ['site_id' => 'id']);
    }

    /**
     * Возвращает подходящую страницу или null, если нет таких.
     *
     * @param string $url Адрес для проверки
     * @return ClientPage|null Страница подходящая адресу
     */
    public function findPageByUrl($url)
    {
        $pages = $this->getClientPages()->orderBy(['priority' => SORT_DESC])->all();
        /** @var ClientPage $page */
        foreach ($pages as $page) {
            if ($page->testUrl($url)) {
                return $page;
            }
        }
        return null;
    }

    /**
     * Возвращает код виджета
     * @return string
     */
    public function getWidgetCode()
    {
        $baseUrl = Variable::sGet('s.settings.baseUrl', $this->user_id, $this->id);
        $result = '<!-- GMCF widget code Start. Do not modify. -->' . PHP_EOL;
        $result .= '<script src="' . $baseUrl . 'cli/cbWidgetLoad.js" type="text/javascript"></script>' .PHP_EOL;
        $params = [
            'baseUrl' => $baseUrl,
            'user_id' => $this->user_id,
            'site_id' => $this->id,
        ];
        $result .= '<script type="text/javascript">cbWidgetLoad('. \yii\helpers\Json::encode($params) .');</script>' . PHP_EOL;
        $result .= '<!-- GMCF widget code End -->' . PHP_EOL;
        return $result;
    }

    /**
     * Возвращает массив строк для замены в шаблонах.
     *
     * Поля непосредственно из таблицы БД:
     * * {id}
     * * {user_id}
     * * {title}
     * * {description}
     * * {url}
     *
     * Другие поля:
     * * {widgetCode} - Код виджета для сайта
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
     *
     * @param string $prefix
     * @return array
     */
    public function tplPlaceholders($prefix = '')
    {
        $result = $this->base_tplPlaceholders($prefix);
        $tz = Yii::$app->timeZone;
        if ($user = $this->user) {
            if ($t = Variable::sGet('u.settings.timezone', $this->user_id, $this->id)) {
                $tz = $t;
            }
            $result = array_merge($result, $user->tplPlaceholders($prefix . 'user.'));
        }
        $result = array_merge($result, static::tplDatetimePlaceholders(time(), $tz));
        $result['{'. $prefix .'widgetCode}'] = $this->widgetCode;
        return $result;
    }

}
