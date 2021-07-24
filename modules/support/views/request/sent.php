<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \app\modules\support\models\SupportForm */

$this->title = Yii::t('app', 'Support request sent.');
$this->params['breadcrumbs'][] = Html::icon('support') . $this->title;

echo '<div class="support-request-form">' . PHP_EOL;

echo AlertFlash::widget();

echo \yii\widgets\DetailView::widget([
    'model' => $model,
    'attributes' => [
        'name',
        'email:email',
        'subject',
    ],
]);

echo Html::tag('strong', $model->getAttributeLabel('message'));
echo '<hr>';
echo Html::tag('div', $model->message);

echo '</div>' . PHP_EOL;
