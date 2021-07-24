<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRuleSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="client-rule-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'site_id');
echo $form->field($model, 'priority');
echo $form->field($model, 'title');
//echo $form->field($model, 'description');
//echo $form->field($model, 'condition_data');
//echo $form->field($model, 'result_data');

echo '<div class="form-group">' . PHP_EOL;
echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']);
echo Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']);
echo '</div>' . PHP_EOL;

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-rule-search"
