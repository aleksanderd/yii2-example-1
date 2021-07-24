<?php

use flyiing\widgets\ActiveForm;
use flyiing\helpers\Html;
use flyiing\helpers\UniHelper;
use flyiing\widgets\AlertFlash;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $source flyiing\translation\models\TSourceMessage */
/* @var $messages flyiing\translation\models\TMessage[] */

$this->title = $source->id .': '. $source->message;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('t-message') . Yii::t('app', 'Translations'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = Html::icon('model-update') . $this->title;
//$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="t-message-edit">' . PHP_EOL;
echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $source,
    'attributes' => [
        'id',
        'category',
        'message',
    ],
]);

$form = ActiveForm::begin([
    'id' => 't-message-edit-form',
    'enableAjaxValidation' => false,
]);

foreach ($messages as $k => $message) {
    echo Html::activeHiddenInput($message, "[$k]id");
    echo Html::activeHiddenInput($message, "[$k]language");
    echo $form->field($message, "[$k]translation")->textarea();
//        ->label(Yii::t('app', 'Translation') . ' (' . $message->language . ')');
}
echo $form->buttons();
ActiveForm::end();

echo '</div>' . PHP_EOL; // class="client-line-view"