<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientLineSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="client-line-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'id' => 'client-line-search-form',
    'action' => ['index'],
    'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'type_id');
echo $form->field($model, 'title');
echo $form->field($model, 'info');
//echo $form->field($model, 'description');

echo '<div class="form-group">' . PHP_EOL;
echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']);
echo Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']);
echo '</div>' . PHP_EOL;

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-line-search"
