<?php

/* @var $this yii\web\View */
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;
use flyiing\widgets\AlertFlash;

/* @var $agreed integer */
/* @var $agreement string */
/* @var $version string */
/* @var \app\models\User $user */
$user = Yii::$app->user->identity;

$title = Yii::t('app', 'Partner agreement');
$this->title = $title;
$this->params['breadcrumbs'][] = $title;

echo HintWidget::widget(['message' => '#PartnerAgreement.hint']);
echo '<div class="partner-agreement-form">' . PHP_EOL;
echo AlertFlash::widget();

$form = ActiveForm::begin([
    'id' => 'partner-agreement-form',
    'enableAjaxValidation' => false,
    'layout' => 'default',
]);

$js = <<<JS
$(window).resize(function() {
    var h = $(window).height() - 400;
    $('#agreement-wrapper').height(h > 200 ? h : 200);
}).trigger('resize');
JS;

$this->registerJs($js);

echo Html::tag('div', $agreement, [
    'id' => 'agreement-wrapper',
    'style' => 'width: 100%; overflow: scroll',
]);

echo '<br/>';
echo \kartik\checkbox\CheckboxX::widget([
    'id' => 'agreed',
    'name' => 'agreed',
    'pluginOptions' => [
        'threeState' => false,
    ],
]);
echo Html::tag('label', Yii::t('app', 'I did read and agree the partner agreement.'), [
    'class' => 'cbx-label',
    'for' => 'agreed',
]);
echo '<hr>';
echo $form->buttons();

ActiveForm::end();

echo '</div>' . PHP_EOL;