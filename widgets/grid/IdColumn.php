<?php

namespace app\widgets\grid;

use flyiing\helpers\Html;
use Yii;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;

class IdColumn extends DataColumn {

    public function __construct($config = [])
    {
        $attribute = ArrayHelper::getValue($config, 'attribute', 'id');
        parent::__construct(ArrayHelper::merge([
            'attribute' => $attribute,
            'content' => function($model) use ($attribute) {
                return Html::a('#' . $model->{$attribute}, ['view', $attribute => $model->{$attribute}], [
                    'target' => '_blank',
                    'data-pjax' => 0,
                ]);
            },
            'hAlign' => 'right',
        ], $config));
    }

}
