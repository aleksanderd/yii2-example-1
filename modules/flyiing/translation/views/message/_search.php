<?php

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientLineSearch */

echo '<div class="client-line-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]);

echo $form->field($model, 'language')->widget(\flyiing\translation\widgets\SelectLanguage::className(), [
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'Any language'),
    ],
]);
echo $form->field($model, 'text');

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Search'),
    ],
]);
ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-line-search"