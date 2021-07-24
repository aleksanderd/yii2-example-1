<?php

use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\VariableSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="variable-search">';

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $this->render('/user/_select', compact('model', 'form'));
echo $form->field($model, 'name');
echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="variable-search"
