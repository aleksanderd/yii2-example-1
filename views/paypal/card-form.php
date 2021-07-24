<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use flyiing\widgets\AlertFlash;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\paypal\CreditCard */

$this->title = Yii::t('app', 'Add credit card');
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('credit-card') . Yii::t('app', 'Credit cards'),
    'url' => ['cards'],
];
$this->params['breadcrumbs'][] = Html::icon('model-create') . Yii::t('app', 'Add');

echo AlertFlash::widget();

echo '<div class="paypal-card-add">' . PHP_EOL;

$form = ActiveForm::begin([]);

echo $form->field($model, 'number');
echo $form->field($model, 'type')->widget(\app\widgets\SelectPaypalCardType::className());
$months = [];
for ($i = 1; $i < 13; $i++) {
    $value = sprintf('%02d', $i);
    $months[$value] = $value;
}
echo $form->field($model, 'expire_month')->widget(Select2::className(), [
    'data' => $months,
    'hideSearch' => true,
]);
$years = [];
$y = intval(date('Y'));
for ($i = $y; $i < $y + 11; $i++) {
    $value = sprintf('%04d', $i);
    $years[$value] = $value;
}
echo $form->field($model, 'expire_year')->widget(Select2::className(), [
    'data' => $years,
    'hideSearch' => true,
]);
echo $form->field($model, 'cvv2');
echo $form->field($model, 'first_name');
echo $form->field($model, 'last_name');
echo $form->field($model, 'billing_address');

echo $form->buttons();

ActiveForm::end();

echo '</div>';