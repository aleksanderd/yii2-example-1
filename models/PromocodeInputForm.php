<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Class PromocodeInputForm
 *
 * @property \app\models\Promocode $promocode
 *
 */
class PromocodeInputForm extends Model
{
    const NEW_USER_TIMEOUT = 3600;

    public $code;
    public $user_id;

    protected $_promocode;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'user_id'], 'required'],
            [['code'], 'exist', 'targetClass' => Promocode::className(), 'targetAttribute' => 'code'],
            [['user_id'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
            ['user_id', 'validatePromocode'],
        ];
    }

    public function validatePromocode($attribute)
    {
        $promocode = $this->promocode;
        $mp = ['code' => $this->code];
        if (empty($promocode)) {
            $this->addError($attribute, Yii::t('app', 'Promotional code {code} is not valid.', $mp));
            return false;
        }
        if ($activation = $promocode->createActivation($this->user_id, $error)) {
            return true;
        } else {
            $this->addError($attribute, $error);
            return false;
        }
    }

    /**
     * @return null|\app\models\Promocode
     */
    public function getPromocode()
    {
        if (!isset($this->_promocode)) {
            $this->_promocode = Promocode::findOne(['code' => $this->code]);
        }
        return $this->_promocode;
    }

    /**
     * Activate promocode for current user.
     * @return null
     */
    public function activate()
    {
        if (!($promocode = $this->promocode)) {
            return false;
        }
        if (!($activation = $promocode->createActivation($this->user_id))) {
            return false;
        }
        return $activation->activate();
    }
}
