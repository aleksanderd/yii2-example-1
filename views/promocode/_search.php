<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PromocodeSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="promocode-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'created');
echo $form->field($model, 'amount');
echo $form->field($model, 'expires');
echo $form->field($model, 'count');
//echo $form->field($model, 'user_id');
//echo $form->field($model, 'description');
//echo $form->field($model, 'new_only');

echo $form->buttons([
    'submit' => ['label' => 'Search'],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="promocode-search"
