<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQueryTestSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="client-query-test-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'site_id');
echo $form->field($model, 'at');
//echo $form->field($model, 'call_info');
//echo $form->field($model, 'data');
//echo $form->field($model, 'title');
//echo $form->field($model, 'description');
//echo $form->field($model, 'options');

echo '<div class="form-group">' . PHP_EOL;
echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']);
echo Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']);
echo '</div>' . PHP_EOL;

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-query-test-search"
