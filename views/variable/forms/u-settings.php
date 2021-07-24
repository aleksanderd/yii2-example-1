<?php

use app\models\UserReferral;
use kartik\select2\Select2;
use kartik\touchspin\TouchSpin;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */

echo $form->field($model, 'language')->widget(\app\widgets\SelectLanguage::className(), ['autoItem' => true]);
echo $form->field($model, 'timezone')->widget(\app\widgets\SelectTimezone::className());
$data = ['none' => Yii::t('app', 'None')];
foreach (ArrayHelper::getValue(Yii::$app->params, 'cssAnimations', []) as $value => $label) {
    if (is_integer($value)) {
        $value = $label;
    }
    $data[$value] = $label;
}
echo $form->field($model, 'pageAnimation')->widget(Select2::className(), [
    'hideSearch' => true,
    'data' => $data,
]);

echo $form->field($model, 'referralScheme')->widget(Select2::className(), [
    'hideSearch' => true,
    'data' => UserReferral::schemeLabels(),
]);
