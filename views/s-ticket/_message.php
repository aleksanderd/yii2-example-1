<?php

use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SMessage */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$css = <<<CSS

.s-message {
    border-left: 11px solid rgba(0, 0, 0, 0.318);
}

.s-message-author {
    border-left: 11px solid rgba(47, 64, 80, 0.382);
}

.s-message-author * {
    border-color: rgba(47, 64, 80, 0.382);
}

.s-message-support {
    border-left: 11px solid rgba(24, 166, 137, 0.318);
}

.s-message-support * {
    border-color: rgba(24, 166, 137, 0.318);
}

CSS;
$this->registerCss($css);

$ticket = $model->ticket;
$isAuthor = $model->user_id == $ticket->user_id;

$fmt = Yii::$app->formatter;

$uLabel = $model->user->username;
if (!$user->isAdmin && !$isAuthor) {
    $uLabel = Yii::t('app', 'Support service');
}
$head = Html::tag('strong', $uLabel);
$head .= Html::tag('span', $fmt->asDatetime($model->created_at) .' #' . $model->id, ['class' => 'pull-right']);

echo Html::tag('a', '', ['name' => 's-message-' . $model->id]);
$class =  $isAuthor ? 's-message-author' : 's-message-support';
echo Html::beginTag('div', ['class' => 'ibox s-message ' . $class]);

echo Html::tag('div', $head, ['class' => 'ibox-title']);
echo Html::tag('div', $model->message, ['class' => 'ibox-content']);

echo Html::endTag('div');
