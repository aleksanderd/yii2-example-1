<?php

use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \app\modules\support\models\SupportForm */

$this->title = Yii::t('app', 'Support request form');
$this->params['breadcrumbs'][] = Html::icon('support') . $this->title;

echo '<div class="support-request-form">' . PHP_EOL;

echo AlertFlash::widget();

echo $this->render('_form', compact('model', 'notifyModel'));

echo '</div>' . PHP_EOL;
