<?php

namespace app\models\query;
use app\models\UserTariff;

/**
 * This is the ActiveQuery class for [[\app\models\User]].
 *
 * @see \app\models\User
 */
class UserQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \app\models\User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function withNoActiveTariff()
    {
        $activeTariffs = UserTariff::find()
            ->select('user_id')
            ->where(['status' => UserTariff::STATUS_ACTIVE]);
        $this->andWhere(['NOT', ['id' => $activeTariffs]]);
        return $this;
    }

}
