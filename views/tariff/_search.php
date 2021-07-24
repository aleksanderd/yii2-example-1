<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TariffSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="tariff-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'title');
echo $form->field($model, 'desc');
echo $form->field($model, 'desc_details');
echo $form->field($model, 'desc_internal');
//echo $form->field($model, 'renewable');
//echo $form->field($model, 'price');
//echo $form->field($model, 'lifetime');
//echo $form->field($model, 'queries');
//echo $form->field($model, 'minutes');
//echo $form->field($model, 'messages');
//echo $form->field($model, 'space');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="tariff-search"
