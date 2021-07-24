<?php

use app\models\Tariff;
use app\models\UserTariff;
use flyiing\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariffSearch */

echo '<div class="user-tariff-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['admin'],
    'method' => 'get',
]);

echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All users'),
        'allowClear' => true,
    ],
]);

echo $form->field($model, 'tariff_id')->widget(Select2::className(), [
    'data' => ArrayHelper::map(Tariff::find()->orderBy(['title' => SORT_ASC])->all(), 'id', 'title'),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'All tariffs'),
    ],
]);

echo $form->field($model, 'status')->widget(Select2::className(), [
    'data' => UserTariff::statusLabels(),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'All statuses'),
    ],
]);

//Yii::t('app', 'Tomorrow');
//Yii::t('app', 'Last {n} days');
//Yii::t('app', 'Next {n} days');

echo $form->field($model, 'dateRange')->widget(\kartik\daterange\DateRangePicker::className(), [
//    'presetDropdown' => true,
    'convertFormat' => true,
    'hideInput' => true,
    //'language' => 'ru',
    'pluginOptions' => [
        'ranges' => [
//            Yii::t('app', "This month") => ["moment().startOf('month')", "moment().endOf('month')"],
//            Yii::t('app', "Last month") => [
//                "moment().subtract(1, 'month').startOf('month')",
//                "moment().subtract(1, 'month').endOf('month')"
//            ],
//            Yii::t('app', "Last {n} days", ['n' => 30]) => [
//                "moment().startOf('day').subtract(29, 'days')",
//                "moment()"
//            ],
            Yii::t('app', "Last {n} days", ['n' => 7]) => [
                "moment().startOf('day').subtract(6, 'days')",
                "moment()"
            ],
            Yii::t('app', "Yesterday") => [
                "moment().startOf('day').subtract(1,'days')",
                "moment().endOf('day').subtract(1,'days')"
            ],
            Yii::t('app', "Today") => ["moment().startOf('day')", "moment()"],
            Yii::t('app', "Tomorrow") => [
                "moment().startOf('day').add(1, 'days')",
                "moment().endOf('day').add(1,'days')"
            ],
            Yii::t('app', "Next {n} days", ['n' => 7]) => [
                "moment()",
                "moment().startOf('day').add(7, 'days')"
            ],
        ],
        'locale' => [
            'format' => 'd.m.yy',
        ],
        'opens' => 'left'
    ],
]);

echo $form->field($model, 'dateRangeSubj')->widget(Select2::className(), [
    'data' => [
        'started_at,lifetimeEnd,finished_at' => Yii::t('app', 'All timestamps'),
        'started_at' => $model->getAttributeLabel('started_at'),
        'lifetimeEnd' => $model->getAttributeLabel('lifetimeEnd'),
        'finished_at' => $model->getAttributeLabel('finished_at'),
    ],
    'hideSearch' => true,
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="user-tariff-search"
