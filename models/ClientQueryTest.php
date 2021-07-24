<?php

namespace app\models;

use app\base\tplModel;
use Yii;

/**
 * This is the model class for table "{{%client_query_test}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $at
 * @property string $call_info
 * @property string $data
 * @property string $title
 * @property string $description
 * @property string $options
 *
 * @property ClientRule $rule
 * @property ClientSite $site
 * @property User $user
 */
class ClientQueryTest extends \yii\db\ActiveRecord
{

    use tplModel;

    public $options;

    public function afterFind()
    {
        $this->options = unserialize($this->options_data);
    }

    public function beforeSave($insert)
    {
        if (is_array($this->options)) {
            $this->options_data = serialize($this->options);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_query_test}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_id', 'at'], 'integer'],
            [['data', 'options'], 'safe'],
            [['call_info', 'title'], 'string', 'max' => 70],
            [['description'], 'string', 'max' => 255],
            [['user_id', 'site_id', 'title'], 'unique', 'targetAttribute' => ['user_id', 'site_id', 'title'], 'message' => 'The combination of User ID, Site ID and Title has already been taken.']
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
            'at' => Yii::t('app', 'At'),
            'call_info' => Yii::t('app', 'Call Info'),
            'data' => Yii::t('app', 'Data'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'options' => Yii::t('app', 'Options'),
        ];
    }

    /**
     * @return ClientQuery
     */
    public function getClientQuery()
    {
        return new ClientQuery([
            'test_id' => $this->id,
            'user_id' => $this->user_id,
            'site_id' => $this->site_id,
            'at' => $this->at,
            'call_info' => $this->call_info,
            'data' => $this->data,
        ]);
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getAt_Tz()
    {
        $formatter = Yii::$app->getFormatter();;
        return $formatter->asTimestamp($formatter->format($this->at, 'datetime'));
    }
/*
    public function setAt_Tz($value)
    {
        //$this->at = $value;
        //return;
        $formatter = Yii::$app->getFormatter();
        $t1 = $formatter->format($value, 'datetime');
        $dt = new \DateTime('@' . $value, new \DateTimeZone(Yii::$app->timeZone));
        $dt2 = new \DateTime('@' . $value, new \DateTimeZone('UTC'));
        $t2 = $formatter->format($dt, 'datetime');
        $t3 = $dt->getTimestamp();
        $dt->setTimezone(new \DateTimeZone('UTC'));
        $t4 = $dt->getTimestamp();
        $t4 = $dt->getTimestamp();

    }

/*
    public function getAt_Full()
    {
        $f = Yii::$app->formatter->asDatetime($this->at, 'short');
        return Yii::$app->formatter->asDatetime($this->at, 'long');
        //return date('Y-m-d H:i:s', $this->at);
    }

    public function setAt_Full($value)
    {
        $this->at = Yii::$app->formatter->asTimestamp($value);
    }
*/
}
