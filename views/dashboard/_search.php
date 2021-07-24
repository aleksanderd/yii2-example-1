<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\forms\BasePeriodFilter */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'base-search-form',
    'method' => 'GET',
    'layout' => 'inline',
]);

//echo Html::tag('div', '&nbsp;', ['class' => 'col-sm-4   ']);
if (count($user->subjectUsers) > 1) {
    echo $form->field($searchModel, 'user_id', [
        'options' => [
            'class' => 'form-group',
            'style' => 'min-width: 220px; padding-right: 5px;',
        ],
    ])->widget(\app\widgets\SelectUser::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'All users'),
            'allowClear' => $user->isAdmin,
        ],
    ]);
} else {
    //echo Html::tag('div', '&nbsp;', ['class' => 'col-sm-3']);
    echo Html::activeHiddenInput($searchModel, 'user_id');
}

echo $form->field($searchModel, 'period', [
    'options' => [
        'class' => 'form-group',
        'style' => 'min-width: 150px; padding-right: 5px;',
    ],
])->widget(\kartik\select2\Select2::className(), [
    'hideSearch' => true,
    'data' => $searchModel->periodLabels(),
]);

echo $form->buttons(['submit' => [
    'label' => Yii::t('app', 'Refresh'),
//    'options' => [
//        'class' => 'btn btn-sm btn-block btn-primary',
//    ],
]], [
//    'class' => 'col-sm-2'
]);

ActiveForm::end();
