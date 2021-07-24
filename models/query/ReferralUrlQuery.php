<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\ReferralUrl]].
 *
 * @see \app\models\ReferralUrl
 */
class ReferralUrlQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\ReferralUrl[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\ReferralUrl|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}