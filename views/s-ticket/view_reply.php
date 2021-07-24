<?php

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model app\models\SMessage */

$this->title = $model->ticket->title;
$this->params['wrapperClass'] = 'gray-bg';
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('sticket') . Yii::t('app', 'Support tickets'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;

echo '<div class="sticket-reply-view">' . PHP_EOL;

echo AlertFlash::widget();

echo Html::tag('h3', Yii::t('app', 'Message'));

//echo Html::tag('div', $model->message);
echo $this->render('_message', compact('model'));

echo Html::a(Yii::t('app', 'View all messages'),
    ['view', 'id' => $model->ticket_id, '#' => 's-message-' . $model->id]);

echo '</div>' . PHP_EOL;
