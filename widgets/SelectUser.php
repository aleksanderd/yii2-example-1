<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * Виджет селектора для выбора пользователя. Если текущий пользователь не админ, то "рисуем" скрытое поле с `id`
 * текущего пользователя.
 */
class SelectUser extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $config['data'] = ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username');
        }
        parent::__construct($config);
    }

}
