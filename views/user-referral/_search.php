<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserReferralSearch */

echo '<div class="user-referral-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'partner_id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'status');
echo $form->field($model, 'paid');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="user-referral-search"
