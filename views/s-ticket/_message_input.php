<?php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\SMessage|app\models\STicket */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

echo $form->field($model, 'message')->widget(vova07\imperavi\Widget::className(), [
    'settings' => [
        'minHeight' => 160,


        'buttons' => [
//            'format',
            'bold',
            'italic',
//            'deleted',
//            'lists',
//            'image',
//            'file',
//            'link',
//            'horizontalrule'
        ],
    ],
])->label(false);
