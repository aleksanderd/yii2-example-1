<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\STicketSearch */

echo '<div class="sticket-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'site_id');
echo $form->field($model, 'created_at');
echo $form->field($model, 'updated_at');
//echo $form->field($model, 'topic_id');
//echo $form->field($model, 'status');
//echo $form->field($model, 'title');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="sticket-search"
