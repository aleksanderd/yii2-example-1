<?php

use app\models\Variable;
use app\widgets\SelectUserFile;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form flyiing\widgets\ActiveForm */

echo '<div class="panel panel-default">';
echo Html::tag('div', Yii::t('app', 'Text to speech settings'), ['class' => 'panel-heading']);
echo '<div class="panel-body">';

echo $form->field($model, 'line')->input('text');
echo $form->field($model, 'voiceType')->widget(\app\widgets\SelectVoiceType::className(), [
    'language' => Variable::sGet('w.settings.language', $model->user_id, $model->site_id, $model->page_id),
]);

$fileOpts = ['mimeRegex' => '/audio.*/'];

echo Html::tag('h2', Yii::t('app', 'Incoming call message')) . '<hr/>';
echo $form->field($model, 'mIncomingCall')->input('text');
echo $form->field($model, 'mIncomingCallAudio')->widget(SelectUserFile::className(), $fileOpts);

echo Html::tag('h2', Yii::t('app', 'Customer call fail message')) . '<hr/>';
echo $form->field($model, 'mClientCallFailed')->input('text');
echo $form->field($model, 'mClientCallFailedAudio')->widget(SelectUserFile::className(), $fileOpts);

echo '</div>'; // panel-body
echo '</div>'; // panel

