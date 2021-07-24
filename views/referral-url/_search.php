<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ReferralUrlSearch */

echo '<div class="referral-url-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'status');
echo $form->field($model, 'user_id');
echo $form->field($model, 'title');
echo $form->field($model, 'promocode_id');
//echo $form->field($model, 'created_at');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="referral-url-search"
