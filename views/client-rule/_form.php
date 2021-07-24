<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */
/* @var $form app\widgets\ActiveForm */

$tabs = ['general', 'time', 'lines'];
$useTabs = false;

if ($useTabs) {
    $tabActive = 'general';
    $tabsLinksOptions = ['data-toggle' => 'tab', 'role' => 'tab'];
    $tabsLinks = [];
    foreach ($tabs as $tab) {
        $tabsLinks[] = [
            'label' => Yii::t('app', ucfirst($tab)),
            'url' => '#client-rule-form-' . $tab,
            'options' => $tabActive == $tab ? ['class' => 'active'] : [],
            'linkOptions' => $tabsLinksOptions,
        ];
    }
} else {
    $tabsLinks = null;
}

$form = ActiveForm::begin([
    'id' => 'client-rule-form',
    'enableAjaxValidation' => true,
]);

$renderParams = compact('form', 'model');

if ($useTabs) {
    echo '<div class="tab-content">' . PHP_EOL;
    foreach ($tabs as $tab) {
        echo Html::tag('div', $this->render('_form_' . $tab, $renderParams), [
            'id' => 'client-rule-form-' . $tab,
            'class' => 'tab-pane fade' . ($tabActive == $tab ? ' in active' : ''),
        ]);
    }
    echo '</div>' . PHP_EOL; // class=tab-content
} else {
    foreach ($tabs as $tab) {
        echo $this->render('_form_' . $tab, $renderParams);
    }
}

echo $form->buttons();

ActiveForm::end();

echo '<br><br>';

