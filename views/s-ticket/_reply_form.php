<?php

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SMessage */

$form = ActiveForm::begin([
    'id' => 'smessage-form',
    'action' => ['reply', 'id' => $model->ticket_id],
    'layout' => 'default',
    //'enableAjaxValidation' => true,
]);

echo Html::activeHiddenInput($model, 'ticket_id');

echo $this->render('_message_input', compact('model', 'form'));


$buttons = [
    'close' => [
        'label' => Yii::t('app', 'Reply and close'),
        'options' => [
            'name' => 'close',
            'value' => 'close',
            'type' => 'submit',
            'class' => 'btn btn-success',
        ],
    ],
    'submit' => [
        'label' => Yii::t('app', 'Reply'),
    ],
];

if ($model->ticket->status == \app\models\STicket::STATUS_CLOSED) {
    $buttons = [
        'submit' => [
            'label' => Yii::t('app', 'Reopen and reply'),
        ],
    ];
} else {
    $buttons = [
        'close' => [
            'label' => Yii::t('app', 'Reply and close'),
            'options' => [
                'name' => 'close',
                'value' => 'close',
                'type' => 'submit',
                'class' => 'btn btn-success',
            ],
        ],
        'submit' => [
            'label' => Yii::t('app', 'Reply'),
        ],
    ];
}

echo $form->buttons($buttons, ['class' => 'pull-right']);

ActiveForm::end();
