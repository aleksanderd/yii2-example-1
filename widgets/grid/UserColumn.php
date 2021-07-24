<?php

namespace app\widgets\grid;

use flyiing\helpers\Html;
use Yii;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;

class UserColumn extends DataColumn {

    public function __construct($config = [])
    {
        $attribute = ArrayHelper::getValue($config, 'attribute', 'user');
        parent::__construct(ArrayHelper::merge([
            'attribute' => $attribute . '_id',
            'label' => Yii::t('app', 'User'),
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'placeholder' => Yii::t('app', 'User'),
                    'allowClear' => true,
                ],
            ],
            'content' => function($model) use ($attribute) {
                if (!($user = $model->{$attribute})) {
                    return '-';
                }
                /** @var \app\models\User $user */
                $l = Html::tag('small', '#' . $user->id) .' '. $user->username;
                return Html::a($l, ['/user/admin/info', 'id' => $user->id], [
                    'target' => '_blank',
                    'data-pjax' => 0,
                ]);
            },
        ], $config));
    }

}
