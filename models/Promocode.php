<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the base model class for table "promocode".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $expires_at
 * @property string $code
 * @property string $amount
 * @property integer $count
 * @property integer $user_id
 * @property string $description
 * @property integer $new_only
 *
 * @property-read bool isValidExpires
 * @property-read bool isValidCount
 * @property-read bool isValid
 * @property-read string newOnlyText
 * @property-read string expiresText
 * @property-read string countText
 * @property Payment[] $payments
 * @property User $user
 * @property User[] $users
 * @property PromocodeActivation[] $activations
 */
class Promocode extends ActiveRecord
{
    /**
    * @inheritdoc
    */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%promocode}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'expires_at'], 'safe'],
            [['amount', 'code'], 'required'],
            [['amount'], 'number'],
            [['count', 'user_id', 'new_only'], 'integer'],
            [['count'], 'integer', 'min' => 1],
            [['user_id'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
            [['description'], 'string'],
            [['code',], 'string', 'min' => 6, 'max' => 255],
            ['code', 'unique', 'targetClass' => static::className()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Promocode'),
            'created_at' => Yii::t('app', 'Created at'),
            'expires_at' => Yii::t('app', 'Expires at'),
            'expiresText' => Yii::t('app', 'Expires at'),
            'amount' => Yii::t('app', 'Amount'),
            'count' => Yii::t('app', 'Uses count'),
            'countText' => Yii::t('app', 'Uses count'),
            'user_id' => Yii::t('app', 'Partner'),
            'description' => Yii::t('app', 'Description'),
            'new_only' => Yii::t('app', 'New users promo'),
            'newOnlyText' => Yii::t('app', 'New users promo'),
        ];
    }

    /**
     * Getting user who own this promocode.
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Getting users who used this promocode.
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable(PromocodeActivation::className(), ['promocode_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivations()
    {
        return $this->hasMany(PromocodeActivation::className(), ['promocode_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function getIsValidExpires()
    {
        return !isset($this->expires_at) || $this->expires_at > time();
    }

    /**
     * @return bool
     */
    public function getIsValidCount()
    {
        return !isset($this->count) || $this->count > $this->getActivations()->count();
    }

    /**
     * @return bool
     */
    public function getIsValid()
    {
        return $this->isValidExpires && $this->isValidCount;
    }

    /**
     * Создаёт модель для активации промокода.
     *
     * @param $user
     * @param string $error
     * @return PromocodeActivation|bool
     */
    public function createActivation($user, &$error = '')
    {
        if ($user instanceof User) {
            $user_id = $user->id;
        } else {
            $user_id = $user;
            /** @var \app\models\User $user */
            if (!($user = User::findOne($user_id))) {
                $error = Yii::t('app', 'User not found.');
                return false;
            }
        }
        $mp = ['code' => $this->code];
        if (!$this->isValidCount) {
            $error = Yii::t('app', 'Promotional code {code} is no longer valid. All available codes have been used.', $mp);
            return false;
        }
        if (!$this->isValidExpires) {
            $error = Yii::t('app', 'Promotional code {code} has expired.', $mp);
            return false;
        }
        if ($this->getActivations()->andWhere(['user_id' => $user_id])->exists()) {
            $error = Yii::t('app', 'Promotional code {code} is not valid.', $mp);
            return false;
        }
        if (isset($this->new_only) && $this->new_only && (time() - $user->created_at) > 3600) {
            $error = Yii::t('app', 'Promotional code {code} is for new clients only. If you have recently registered and are experiencing difficulties with code activation, please contact us at', $mp);
            return false;
        }
        return new PromocodeActivation([
            'partner_id' => $this->user_id,
            'user_id' => $user_id,
            'promocode_id' => $this->id,
        ]);
    }

    public function getNewOnlyText()
    {
        return $this->new_only ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
    }

    public function getExpiresText()
    {
        return empty($this->expires_at) ? Yii::t('app', 'Never') : Yii::$app->formatter->asDatetime($this->expires_at);
    }

    public function getCountText()
    {
        return empty($this->count) ? Yii::t('app', 'Unlimited') : Yii::t('app', '{used, number} of {total, number}', [
            'used' => $this->getActivations()->count(),
            'total' => $this->count,
        ]);
    }

}
