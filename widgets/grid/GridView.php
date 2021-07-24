<?php

namespace app\widgets\grid;

use Yii;
use yii\helpers\ArrayHelper;

class GridView extends \kartik\grid\GridView {

    public function __construct($config = [])
    {
        $defaults = ArrayHelper::getValue(Yii::$app->params, 'widgetsDefaults.GridView', []);
        parent::__construct(ArrayHelper::merge($defaults, $config));
    }

}
