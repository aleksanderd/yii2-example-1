<?php

use app\helpers\ViewHelper;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\STicket */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->title;
$this->params['wrapperClass'] = 'gray-bg';
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('sticket') . Yii::t('app', 'Support tickets'),
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="sticket-view">' . PHP_EOL;

echo AlertFlash::widget();

$attributes = [
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => ViewHelper::ticketStatusSpan($model),
    ],
    'id',
    [
        'attribute' => 'site_id',
        'format' => 'raw',
        'value' => (isset($model->site)) ?
            Html::a('#' . $model->site_id .' '. $model->site->title, ['/client-site/view', 'id' => $model->site_id], ['target' => '_blank'])
            : null,
    ],
    'created_at:datetime',
    'updated_at:datetime',
];

if ($user->isAdmin) {
    $attributes = array_merge([
        [
            'attribute' => 'user_id',
            'format' => 'raw',
            'value' => Html::a('#' . $model->user_id .' '. $model->user->username,
                ['/user/admin/info', 'id' => $model->user_id], ['target' => '_blank']),
        ],
    ], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

//echo Html::tag('h3', Yii::t('app', 'Messages'));

/** @var app\models\SMessage[] $messages */
$messages = $model->getSMessages()->orderBy(['created_at' => SORT_ASC])->all();
foreach ($messages as $msg) {

    echo $this->render('_message', ['model' => $msg]);

}

echo '<hr>';

echo $this->render('_reply_form', [
    'model' => $model->createMessage(),
]);

echo '</div>' . PHP_EOL; // class="sticket-view"
