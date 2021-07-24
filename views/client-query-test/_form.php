<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use app\helpers\ModelsHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQueryTest */
/* @var $form app\widgets\ActiveForm */

$anySiteItem = ['id' => 0, 'text' => Yii::t('app', 'Select website')];
$anySiteItemJSON = \yii\helpers\Json::encode($anySiteItem);

$submitOptions = ['type' => 'submit', 'class' => 'btn'];
if ($model->isNewRecord) {
    $submitLabel = Yii::t('app', 'Add');
    Html::addCssClass($submitOptions, 'btn-success');
} else {
    $submitLabel = Yii::t('app', 'Save');
    Html::addCssClass($submitOptions, 'btn-primary');
}

$form = ActiveForm::begin([
    'id' => 'client-query-test-form',
    'enableAjaxValidation' => true,
]);

if (Yii::$app->user->identity->isAdmin) {

    $sitesUrl = Url::toRoute(['client-site/select-list']);
$onUserChangeJS = <<<JS
    var user_id = $(this).val();
    $.ajax({
        data: { user_id: user_id },
        url: '{$sitesUrl}'
    }).done(function(data, status, xhr) {
        $('#clientquerytest-site_id')
            .select2({ data: data, minimumResultsForSearch: Infinity })
            .select2('val', null);
    }).fail(function(xhr, status, thrown) {
        alert('Error: Getting sites list failed :(');
    });
JS;

    $users = \app\models\User::find()->all();
    echo $form->field($model, 'user_id')->widget('kartik\select2\Select2', [
        'data' => ArrayHelper::map($users, 'id', 'username'),
        'hideSearch' => true,
        'options' => [
            'onchange' => new JsExpression($onUserChangeJS),
        ],
    ])->label(Yii::t('user', 'User'));
}

$sites = array_merge(
    [$anySiteItem],
    ModelsHelper::getSitesSelectList($model->user_id)
);
echo $form->field($model, 'site_id')->widget('kartik\select2\Select2', [
    'hideSearch' => true,
    'pluginOptions' => [
        'data' => $sites,
    ],
])->label(Yii::t('app', 'Website'));

echo $form->field($model, 'title')->textInput(['maxlength' => 70]);
echo $form->field($model, 'description')->textInput(['maxlength' => 255]);

echo $form->field($model, 'at')->widget('\kartik\datecontrol\DateControl', [
    'type' => 'datetime',
    'ajaxConversion' => true,
    'options' => [
        'pluginOptions' => [
            'autoclose' => true,
        ],
    ],
]);
echo $form->field($model, 'call_info')->textInput(['maxlength' => 70]);
//echo $form->field($model, 'data')->textarea(['rows' => 6]);
//echo $form->field($model, 'options')->textarea(['rows' => 6]);
echo $form->buttons();

ActiveForm::end();

