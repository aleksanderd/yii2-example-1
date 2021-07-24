<?php

use app\widgets\ActiveForm;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model app\models\ClientPage */
/* @var $notifyModel app\models\variable\UNotify */

$form = ActiveForm::begin([
    'id' => 'client-page-form',
    'enableAjaxValidation' => true,
]);

echo TabsX::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'General'),
            'content' => $this->render('_form_general', compact('form', 'model')),
        ],
        [
            'label' => Yii::t('app', 'Notifications'),
            'content' => $this->render('/variable/forms/u-notify', [
                'model' => $notifyModel,
                'form' => $form,
                'options' => [
                    'disableFin' => true,
                ],
            ]),
        ],
    ],
]);
echo $form->buttons();

ActiveForm::end();
