<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientSite */

$this->params['breadcrumbs'][] = ['label' => Html::icon('model-update') . Yii::t('app', 'Notification test')];

$this->title = Yii::t('app', 'Notification test');
echo '<div class="crud-create client-site-create">' . PHP_EOL;

echo AlertFlash::widget();

echo 'HW';

$form = ActiveForm::begin([
    'id' => 'notification-test-form',
    //'enableAjaxValidation' => true,
]);

echo $form->field($model, 'user_id');
echo $form->field($model, 'site_id');
echo $form->field($model, 'query_id');
echo $form->field($model, 'type');
echo $form->field($model, 'to');
echo $form->field($model, 'from');
echo $form->field($model, 'subject');
echo $form->field($model, 'body');
echo $form->buttons();

ActiveForm::end();

echo '</div>' . PHP_EOL;