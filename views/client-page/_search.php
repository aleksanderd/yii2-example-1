<?php

use app\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\ClientSite;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\ClientPageSearch */
/* @var $form app\widgets\ActiveForm */

echo '<div class="client-page-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'id' => 'client-page-search-form',
//    'layout' => 'inline',
    'action' => ['index'],
    'method' => 'get',
]);
/*
echo $form->field($model, 'id');
echo $form->field($model, 'user_id');
echo $form->field($model, 'site_id');
echo $form->field($model, 'title');
echo $form->field($model, 'type');
echo $form->field($model, 'pattern');
*/

$sites = [];
foreach (Yii::$app->user->identity->clientSites as $site) {
    $sites[] = ['id' => $site->id, 'text' => $site->title];
}
// TODO сделать зависимые селекторы для админа, с выбором юзера\

echo $form->field($model, 'site_id')->widget(Select2::className(), [
    'data' => ArrayHelper::map(ClientSite::findAll(['user_id' => Yii::$app->user->id]), 'id', 'title'),
    'hideSearch' => true,
    'options' => [
//        'style' => 'width: 300px',
    ],
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'All websites'),
//        'width' => 'resolve', // TODO чет не работает :(
    ],
])->label(Yii::t('app', 'Website'));

echo $form->buttons([
    'submit' => ['label' => Yii::t('app', 'Search')],
    //'reset',
]);

ActiveForm::end();

echo '</div><hr>' . PHP_EOL; // class="client-page-search"
