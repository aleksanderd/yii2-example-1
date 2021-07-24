<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ModalTextSearch */

echo '<div class="modal-text-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'title');
echo $form->field($model, 'submit');
echo $form->field($model, 'description');

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="modal-text-search"
