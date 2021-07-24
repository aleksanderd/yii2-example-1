<?php

use app\models\Payment;
use app\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\PaymentSearch */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

echo '<div class="payment-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'method' => 'get',
]);

if ($user->isAdmin) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'All users'),
            'allowClear' => true,
        ],
    ]);

}

echo $form->field($model, 'status')->widget(Select2::className(), [
    'data' => Payment::statusLabels(),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'No matters'),
    ],
]);

echo $form->field($model, 'description');

echo $form->field($model, 'dateRange')->widget(\kartik\daterange\DateRangePicker::className(), [
//    'presetDropdown' => true,
    'convertFormat' => true,
    'hideInput' => true,
    //'language' => 'ru',
    'pluginOptions' => [
        'ranges' => [
            Yii::t('app', "This month") => ["moment().startOf('month')", "moment().endOf('month')"],
            Yii::t('app', "Last month") => [
                "moment().subtract(1, 'month').startOf('month')",
                "moment().subtract(1, 'month').endOf('month')"
            ],
            Yii::t('app', "Last {n} days", ['n' => 30]) => [
                "moment().startOf('day').subtract(29, 'days')",
                "moment()"
            ],
            Yii::t('app', "Last {n} days", ['n' => 7]) => [
                "moment().startOf('day').subtract(6, 'days')",
                "moment()"
            ],
            Yii::t('app', "Yesterday") => [
                "moment().startOf('day').subtract(1,'days')",
                "moment().endOf('day').subtract(1,'days')"
            ],
            Yii::t('app', "Today") => ["moment().startOf('day')", "moment()"],
//            Yii::t('app', "Tomorrow") => [
//                "moment().startOf('day').add(1, 'days')",
//                "moment().endOf('day').add(1,'days')"
//            ],
//            Yii::t('app', "Next {n} days", ['n' => 7]) => [
//                "moment()",
//                "moment().startOf('day').add(7, 'days')"
//            ],
        ],
        'locale' => [
            'format' => 'd.m.yy',
        ],
        'opens' => 'left'
    ],
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL;
