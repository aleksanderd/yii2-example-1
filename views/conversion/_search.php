<?php

use flyiing\helpers\Html;
use app\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\ConversionSearch;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $searchModel \app\models\ConversionSearch */

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$form = ActiveForm::begin([
    'id' => 'conversion-search-form',
    'method' => 'get',
    'enableAjaxValidation' => false,
]);


$dtStartId = Html::getInputId($searchModel, 'dtStart');
$dtEndId = Html::getInputId($searchModel, 'dtEnd');
$js = <<<JS
function _dtSet(start, end)
{
    console.log('_dtSet: ' + start + ', ' + end);
    if (start !== undefined) {
        var \$start = jQuery('#$dtStartId-disp');
        \$start.val(start).trigger('change');
    }
    if (end !== undefined) {
        var \$end = jQuery('#$dtEndId-disp');
        \$end.val(end).trigger('change');
    }
}
JS;
$this->registerJs($js, \yii\web\View::POS_BEGIN);

$f = Yii::$app->formatter;
$time = time();
$dateToday = $f->asDate($time);

$datePresets = [
    Yii::t('app', 'Today') => [
        'start' => $dateToday,
        'end' => '',
    ],
    Yii::t('app', 'Yesterday') => [
        'start' => $f->asDate($time - 86400),
        'end' => $f->asDate($time -86400),
    ],
    Yii::t('app', 'This week') => [
        'start' => $f->asDate(strtotime('this week', $time)),
        'end' => '',
    ],
    Yii::t('app', 'This month') => [
        'start' => $f->asDate(strtotime('first day of', $time)),
        'end' => '',
    ],
    Yii::t('app', 'Last month') => [
        'start' => $f->asDate(strtotime('first day of previous month', $time)),
        'end' => $f->asDate(strtotime('last day of previous month', $time)),
    ],
    Yii::t('app', 'All time') => [
        'start' => '',
        'end' => '',
    ],

];

$buttons = [];
foreach ($datePresets as $pTitle => $p) {
    $buttons[] = [
        'tagName' => 'a',
        'label' => $pTitle,
        'options' => [
            'onClick' => sprintf('_dtSet("%s", "%s")', $p['start'], $p['end']),
            //'class' => 'btn',
            //'style' => 'color: white; font-weight: bold',
        ],
    ];
}
$buttonsContent = \yii\bootstrap\ButtonGroup::widget([
    'buttons' => $buttons,
    'options' => [
        'class' => 'btn-group btn-group-xs pull-right'
    ],
]);

echo Html::tag('span', $buttonsContent, ['class' => 'col-md-8 col-sm-12']);
echo '<div class="clearfix"></div>';

$datePickerOptions = [
    'type' => DateControl::FORMAT_DATE,
    'options' => [
//        'pickerButton' => false,
        'pluginOptions' => [
            'autoclose' => true,
        ],
    ],
];
echo $form->field($searchModel, 'dtStart')->widget(DateControl::className(), $datePickerOptions);
echo $form->field($searchModel, 'dtEnd')->widget(DateControl::className(), $datePickerOptions);

$selectSiteOptions = [
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'All websites'),
    ],
];
if (count($user->subjectUsers) > 1) {
    echo $form->field($searchModel, 'user_id')->widget(\app\widgets\SelectUser::className(), [
        'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
        'pluginOptions' => [
            'allowClear' => true,
            'placeholder' => Yii::t('app', 'All users'),
        ],
    ]);
    echo $form->field($searchModel, 'site_id')->widget(DepDrop::className(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => [$searchModel->site_id => ''],
        'pluginOptions' => [
            'placeholder' => Yii::t('app', 'All websites'),
            'url' => Url::toRoute(['client-site/select-list']),
            'depends' => [Html::getInputId($searchModel, 'user_id')],
            'initialize' => true,
        ],
        'select2Options' => $selectSiteOptions,
    ]);
} else {
    $searchModel->user_id = $user->id;
    echo Html::activeHiddenInput($searchModel, 'user_id');
    $selectSiteOptions['data'] = [];
    echo $form->field($searchModel, 'site_id')->widget(Select2::className(), $selectSiteOptions);
}

echo $form->field($searchModel, 'groupBy')->widget(Select2::className(), [
    'data' => ConversionSearch::groupByLabels(),
    'hideSearch' => true,
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'Do not group'),
    ],
]);

echo $form->buttons();
ActiveForm::end();
