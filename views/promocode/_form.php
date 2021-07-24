<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use kartik\money\MaskMoney;

/* @var $this yii\web\View */
/* @var $model app\models\Promocode */
/* @var $form app\widgets\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'promocode-form',
    'enableAjaxValidation' => true,
]);
echo $form->field($model, 'code');
echo $form->field($model, 'amount')->widget(MaskMoney::className(), [
    'pluginOptions' => [
        'affixesStay' => false,
        'allowNegative' => false,
    ],
])->label($model->getAttributeLabel('amount') . ' ' . Html::icon(Yii::$app->currencyCode));
echo $form->field($model, 'expires_at')->widget(DateTimePicker::classname(), [
    'options' => ['placeholder' => Yii::t('app', 'Never')],
    'pluginOptions' => [
        'autoclose' => true
    ]
]);
echo $form->field($model, 'count')->textInput(['placeholder' => Yii::t('app', 'Unlimited')]);
//echo $form->field($model, 'user_id')->textInput();
echo $this->render('/user/_select', [
    'form' => $form,
    'model' => $model,
    'placeholder' => Yii::t('app', 'Empty'),
]);
echo $form->field($model, 'description')->textarea(['rows' => 6]);
echo $form->field($model, 'new_only')->checkbox();

echo $form->buttons();

ActiveForm::end();
