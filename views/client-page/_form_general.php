<?php

use app\helpers\ModelsHelper;
use app\models\ClientPage;
use app\models\User;
use flyiing\helpers\Html;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\ClientPage */
/* @var $form app\widgets\ActiveForm */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

if ($model->isNewRecord && count($user->subjectUsers) > 1) {
    echo $form->field($model, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'hideSearch' => count($user->subjectUsers) < 7,
    ])->label(Yii::t('app', 'User'));
} else {
    echo Html::activeHiddenInput($model, 'user_id');
}

echo $form->field($model, 'title')->textInput(['maxlength' => true]);
echo $form->field($model, 'site_id')->widget(DepDrop::className(), [
    'type' => DepDrop::TYPE_SELECT2,
    'data' => [$model->site_id => ''],
    'pluginOptions' => [
        'url' => Url::toRoute('client-site/select-list'),
        'initDepends' => ['clientpage-user_id'],
        'depends' => ['clientpage-user_id'],
        'initialize' => true,
    ],
    'select2Options' => [
        'hideSearch' => true,
    ],
])->label(Yii::t('app', 'Website'));

echo $form->field($model, 'ignoreDomain')->checkbox();
echo $form->field($model, 'ignoreParams')->checkbox();
$types = ModelsHelper::getPatternTypesSelectList();
if ($model->type < 1) {
    $model->type = $types[0]['id'];
}
echo $form->field($model, 'type')->widget(Select2::className(), [
    'data' => ClientPage::getTypeLabels(),
    'hideSearch' => true,
]);
echo $form->field($model, 'pattern')->textInput(['maxlength' => true]);

echo $form->field($model, 'priority')->textInput();
