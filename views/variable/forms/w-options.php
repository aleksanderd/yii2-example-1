<?php

use app\models\variable\WTextsEn;
use app\models\variable\WTextsRu;
use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use kartik\touchspin\TouchSpin;
use yii\bootstrap\Tabs;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model app\models\variable\WOptions */
/* @var $form app\widgets\ActiveForm */

$this->params['wrapperClass'] = 'gray-bg';

$this->registerCssFile('@web/cli/cbWidget.css');
$this->registerJsFile('@web/cli/cbWidget.js');

$key = [
    'user_id' => $model->user_id,
    'site_id' => $model->site_id,
    'page_id' => $model->page_id,
];

$vals = $model->getValues();
if ($vals['language'] == 'ru') {
    $wTexts = new WTextsRu($key);
} else {
    $wTexts = new WTextsEn($key);
}
$vals['textsOptions'] = $wTexts->getValues();
$vals = Json::encode($vals);

$js = <<<JS

var cbwOptions = $vals;
var cbwDemo = new CBWidgetDemo(cbwOptions);
cbwDemo.create();

var inputs = $('input[name^="WOptions"], select[name^="WOptions"]');
inputs.on('change', function() {
    var val = this.value, name = this.name;
    //console.log(name);
    if (val.length < 1) {
        val = $('[name="vw_input_' + name  + '"]').val();
    }
//    console.log(val);
//    cbwDemo.restart(618);
    cbwDemo.updateOption(name, val);
});

JS;

$this->registerJs($js);

$items = [];

$basic = HintWidget::widget(['message' => '#WOptions.basic.hint']) . '<hr/>';

$basic .= $form->field($model, 'mode')->widget(Select2::className(), [
    'data' => [
        100 => Yii::t('app', 'Automatic mode'),
        10 => Yii::t('app', 'Manual mode'),
        0 => Yii::t('app', 'Disable widget'),
    ],
    'hideSearch' => true,
]);
$basic .= $form->field($model, 'language')->widget(\app\widgets\SelectLanguage::className());
$basic .= $form->field($model, 'startDelay')->widget(TouchSpin::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 9999,
        'boostat' => 5,
        'postfix' => Yii::t('app', 's.'),
    ],
]);
$basic .= $form->field($model, 'restoreInfo')->widget(Select2::className(), [
    'data' => [
        0 => Yii::t('app', 'Do not restore'),
        1 => Yii::t('app', 'Last call values'),
        2 => Yii::t('app', 'Last valid values'),
        3 => Yii::t('app', 'Last input values'),
    ],
    'hideSearch' => true,
]);
$basic .= $form->field($model, 'defaultPrefix')->widget(\app\widgets\SelectPhonePrefix::className());
$basic .= $form->field($model, 'mobileMode')->widget(Select2::className(), [
    'data' => [
        100 => Yii::t('app', 'Normal mode'),
        10 => Yii::t('app', 'Disable browser scale'),
        0 => Yii::t('app', 'Disable widget'),
    ],
    'hideSearch' => true,
]);
$basic .= $form->field($model, 'noRuleMode')->widget(Select2::className(), [
    'data' => [
        100 => Yii::t('app', 'Offer deferred call'),
        0 => Yii::t('app', 'Disable widget'),
    ],
    'hideSearch' => true,
]);
$basic .= $form->field($model, 'deferredTries')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 20,
        'step' => 1,
        'hide_min_max' => true,
//        'hide_from_to' => true,
        'grid' => true,
    ],
]);

$items = [
    [
        'label' => Yii::t('app', 'Basic'),
        'content' => Html::tag('div', $basic, ['class' => 'panel-body']),
    ],
    [
        'label' => Yii::t('app', 'Live button'),
        'content' => $this->render('w-button-options', [
            'model' => $model->buttonOptions,
            'form' => $form,
        ]),
    ],
    [
        'label' => Yii::t('app', 'Modal window'),
        'content' => $this->render('w-modal-options', [
            'model' => $model->modalOptions,
            'form' => $form,
        ]),
    ],
    [
        'label' => Yii::t('app', 'Triggers'),
        'content' => $this->render('w-triggers-options', [
            'model' => $model->triggersOptions,
            'form' => $form,
        ]),
    ],
];

$tabs = Tabs::widget([
//    'navType' => 'nav-pills',
    'items' => $items,
]);

echo Html::tag('div', $tabs, ['class' => 'tabs-container']);

echo '<br>';
