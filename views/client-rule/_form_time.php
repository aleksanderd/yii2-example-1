<?php

use app\helpers\DataHelper;
use app\models\Variable;
use app\widgets\time\TimeWidget;

/* @var $this yii\web\View */
/* @var $model app\models\ClientRule */
/* @var $form app\widgets\ActiveForm */

$css = <<<CSS

.time-widget * {
    min-height: 0;
}

.time-widget tr.weekdays * {
    text-align: center;
    margin: 0;
    padding: 0 1px;
    font-weight: bold;
    font-size: 90%;
}

.time-widget tr.weekdays td:first-child {
    padding-right: 5px;
}

.time-widget tr.hours * {
    margin: 0;
    padding: 0;
    font-size: 90%;
    font-weight: bold;
}
CSS;
$this->registerCss($css);

if ($timezone = Variable::sGet('u.settings.timezone', $model->user_id, $model->site_id, $model->page_id)) {
    $defaultTimezoneText = DataHelper::timezoneFull($timezone);
} else {
    $defaultTimezoneText = Yii::$app->timeZone;
}

echo $form->field($model, 'timezone')->widget(\app\widgets\SelectTimezone::className(), [
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => Yii::t('app', 'User timezone: {tz}', ['tz' => $defaultTimezoneText]),
    ],
]);

echo $form->field($model, 'hours', [
    'enableAjaxValidation' => false,
])->widget(TimeWidget::classname());

