<?php

use app\widgets\hint\HintWidget;
use yii\helpers\Html;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use app\models\Notification;

/* @var $this yii\web\View */
/* @var $model app\models\Notification */

$this->title = Yii::t('app', 'Notification: ') . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Notifications'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo HintWidget::widget(['message' => '#NotificationView.hint']);
echo '<div class="locale-text-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'user.username',
        'at:datetime',
        'to:email',
        'from:email',
        'subject',
        'body:ntext',
        'description',
        [
            'label' => Yii::t('app', 'Query'),
            'format' => 'raw',
            'value' => isset($model->query) ? Html::a('#' . $model->query_id, ['client-query/view', 'id' => $model->query_id]) : '-',
        ],
        [
            'label' => Yii::t('app', 'Website'),
            'format' => 'raw',
            'value' => isset($model->site) ? Html::a('#' . $model->site_id, ['client-site/view', 'id' => $model->site_id]) : '-',
        ],
        [
            'label' => Yii::t('app', 'Page'),
            'format' => 'raw',
            'value' => isset($model->page) ? Html::a('#' . $model->page_id, ['client-page/view', 'id' => $model->page_id]) : '-',
        ],
        [
            'attribute' => 'type',
            'value' => Notification::typeLabels()[$model->type],
        ],
    ],
]);

echo '</div>' . PHP_EOL; // class="locale-text-view"
