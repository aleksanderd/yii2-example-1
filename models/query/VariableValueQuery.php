<?php

namespace app\models\query;
use app\models\Variable;

/**
 * This is the ActiveQuery class for [[\app\models\VariableValue]].
 *
 * @see \app\models\VariableValue
 */
class VariableValueQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \app\models\VariableValue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\VariableValue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function withName($name)
    {
        $v = Variable::find()
            ->select('id')
            ->where(['name' => $name]);
        return $this->andWhere(['id' => $v]);
    }

    public function withValue($value, $op = '')
    {
        return $this->andWhere([$op, 'value_data', $value]);
    }

}