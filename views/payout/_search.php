<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PayoutSearch */

echo '<div class="payout-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'transaction_id');
echo $form->field($model, 'status');
echo $form->field($model, 'amount');
//echo $form->field($model, 'comment');
//echo $form->field($model, 'created_at');
//echo $form->field($model, 'updated_at');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="payout-search"
