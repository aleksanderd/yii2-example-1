<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */
/* @var $form app\widgets\ActiveForm */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$userPlaceholder = Yii::t('app', 'All users');
$sitePlaceholder = Yii::t('app', 'All websites');
$pagePlaceholder = Yii::t('app', 'All pages');

if ($model->isNewRecord && count($user->subjectUsers) > 1) {
    echo $form->field($model, 'user_id')->widget(Select2::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'hideSearch' => count($user->subjectUsers) < 7,
        'pluginOptions' => [
            'placeholder' => $userPlaceholder,
            'allowClear' => true,
        ],
    ])->label(Yii::t('app', 'User'));
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}

echo $form->field($model, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'placeholder' => $sitePlaceholder,
        'url' => Url::toRoute('client-site/select-list'),
        'depends' => ['clientrule-user_id'],
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
])->label(Yii::t('app', 'Website'));

echo $form->field($model, 'page_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->page_id => ''],
    'pluginOptions' => [
        'placeholder' => $pagePlaceholder,
        'url' => Url::toRoute('client-page/select-list'),
        'initDepends' => ['clientrule-user_id'],
        'depends' => ['clientrule-site_id'],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ],
])->label(Yii::t('app', 'Page'));

echo $form->field($model, 'active')->widget(Select2::className(), [
    'data' => \app\models\ClientRule::activeLabels(),
    'hideSearch' => true,
]);
echo $form->field($model, 'priority')->textInput();
echo $form->field($model, 'title')->textInput(['maxlength' => 70]);
echo $form->field($model, 'description')->textInput(['maxlength' => 255]);
