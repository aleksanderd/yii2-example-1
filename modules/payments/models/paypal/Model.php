<?php

namespace app\models\paypal;

use yii\helpers\ArrayHelper;

class Model extends \yii\base\Model
{

    public function addPaypalErrors($data)
    {
        $result = 0;
        foreach (ArrayHelper::getValue($data, 'details', []) as $item) {
            if (($fields = ArrayHelper::getValue($item, 'field', false)) === false) {
                continue;
            }
            $error = ArrayHelper::getValue($item, 'issue', 'error');
            $fields = explode(',', $fields);
            foreach ($fields as $field) {
                $field = trim($field);
                if ($this->hasProperty($field)) {
                    $this->addError($field, $error);
                    $result++;
                }
            }
        }
        return $result;
    }

}