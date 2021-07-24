<?php

use app\models\ClientQuerySearch;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use app\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQuerySearch */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

echo '<div class="client-query-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'id' => 'client-query-search-form',
    //'action' => ['index'],
    'method' => 'get',
]);

if ($user->isAdmin) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'All users'),
            'allowClear' => true,
        ],
    ]);
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}

echo $form->field($model, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'All websites'),
        'url' => Url::toRoute(['client-site/select-list']),
        'depends' => [Html::getInputId($model, 'user_id')],
        'initialize' => true,
    ],
    'select2Options' => [
        //'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
])->label(Yii::t('app', 'Website'));

echo $form->field($model, 'status')->widget(Select2::className(), [
    'data' => ClientQuerySearch::statusFilterOptions(),
    'hideSearch' => true,
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'No matters'),
        'allowClear' => true,
    ],
]);

echo $form->field($model, 'user_tariff_id')->widget(Select2::className(), [
    'data' => ClientQuerySearch::tariffFilterOptions(),
    'hideSearch' => true,
    'pluginOptions' => [
        'placeholder' => Yii::t('app', 'No matters'),
        'allowClear' => true,
    ],
])->label(Yii::t('app', 'Tariff'));

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

echo $form->field($model, 'groupBy')->widget(Select2::className(), [
    'data' => ClientQuerySearch::groupByLabels(),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'Do not group'),
    ],
]);

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-query-search"
