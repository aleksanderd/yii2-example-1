<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BlackCallInfoSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="black-call-info-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'call_info');
echo $form->field($model, 'comment');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="black-call-info-search"
