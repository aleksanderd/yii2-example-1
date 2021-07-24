<?php

use app\widgets\ActiveForm;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\UserTariff */
/* @var $paramsModel yii\base\DynamicModel */

$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'User tariffs'),
    'url' => ['index']
];

$this->title = Yii::t('app', 'Activate tariff: ') . $model->title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Activate');

echo '<div class="user-tariff-activate">' . PHP_EOL;
echo AlertFlash::widget();

$form = ActiveForm::begin([
    'id' => 'user-tariff-buy-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
]);

echo $form->field($paramsModel, 'start')->widget(\kartik\select2\Select2::className(), [
    'data' => [
        0 => Yii::t('app', 'Activate immediately'),
        1 => Yii::t('app', 'Activate at predefined time'),
    ],
    'hideSearch' => true,
    'options' => [
        'onchange' => 'document.getElementById("dynamicmodel-started_at").disabled = this.value != 1;',
    ]
])->label(Yii::t('app', 'Tariff start time'));
echo $form->field($paramsModel, 'started_at')->widget(\kartik\datetime\DateTimePicker::className(), [
    'options' => [
        'disabled' => ($paramsModel->start != 1),
    ],
])->label(Yii::t('app', 'Tariff will be started at'));
if ($model->renewable) {
    echo $form->field($model, 'renew')->widget(\kartik\checkbox\CheckboxX::className(), [
        'pluginOptions' => [
            'threeState' => false,
        ],
    ]);
}
echo Html::hiddenInput('confirm', '1');

echo $form->buttons([
    'submit' => [
        'label' => Yii::t('app', 'Activate the tariff'),
    ],
]);

ActiveForm::end();


echo '</div>' . PHP_EOL;
