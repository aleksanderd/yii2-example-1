<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */
/* @var $form app\widgets\ActiveForm */

$tariff = $model->tariff;

$tariff = $model->tariff;
$tDetails = Html::tag('span', $this->render('_select_item', compact('tariff')), [
    'class' => 'col-md-offset-2 col-md-8'
]);
echo Html::tag('div', $tDetails, ['class' => 'row']);

$form = ActiveForm::begin([
    'id' => 'user-tariff-buy-form',
    'enableAjaxValidation' => false,
]);

if ($tariff->renewable) {
    echo $form->field($model, 'renew')->widget(\kartik\checkbox\CheckboxX::className(), [
        'pluginOptions' => [
            'threeState' => false,
        ],
    ]);
}
echo Html::hiddenInput('confirm', '1');

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Confirm and pay the tariff'),
    ],
]);

ActiveForm::end();
