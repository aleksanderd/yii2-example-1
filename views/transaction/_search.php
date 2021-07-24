<?php

use app\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\TransactionSearch */
/* @var $form app\widgets\ActiveForm */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

echo '<div class="transaction-search">' . PHP_EOL;

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

    echo $form->field($model, 'paymentLink')->widget(Select2::className(), [
        'data' => [
            'yes' => Yii::t('app', 'Transactions with payments'),
            'no' => Yii::t('app', 'Transactions without payments'),
        ],
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
            'placeholder' => Yii::t('app', 'No matters'),
        ],
    ]);

}

echo $form->field($model, 'come')->widget(Select2::className(), [
    'data' => [
        'in' => Yii::t('app', 'Income transactions'),
        'out' => Yii::t('app', 'Outcome transactions'),
    ],
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'No matters'),
    ],
]);

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
//    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="transaction-search"
