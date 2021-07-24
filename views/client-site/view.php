<?php

use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ClientSite */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Html::icon('client-site') . Yii::t('app', 'Websites'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::icon('model-view') . $this->title;

$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'site' => $model,
    'items' => ['lines', 'rules', 'pages'],
]);

$actions = UniHelper::getModelActions($model, [
    'delete',
    'update',
]);

$kar = [
    'user_id' => $model->user_id,
    'site_id' => $model->id,
];
$actions = array_merge($actions, [

    \yii\bootstrap\ButtonDropdown::widget([
        'label' => Html::icon('wrench') . Yii::t('app', 'Settings'),
        'encodeLabel' => false,
        'dropdown' => [
            'items' => [
                [
                    'label' => Yii::t('app', 'Widget'),
                    'url' => Url::to(array_merge(['/variable/form', 'name' => 'w-options'], $kar)),
                ],
                [
                    'label' => Yii::t('app', 'Texts'),
                    'url' => Url::to(array_merge(['/variable/form', 'name' => 'w-texts'], $kar)),
                ],
                [
                    'label' => Yii::t('app', 'Notifications'),
                    'url' => Url::to(array_merge(['/variable/form', 'name' => 'u-notify'], $kar)),
                ],
                [
                    'label' => Yii::t('app', 'Calls'),
                    'url' => Url::to(array_merge(['/variable/form', 'name' => 'c-settings'], $kar)),
                ],
            ],
        ],
        'options' => [
            'class' => 'btn-white',
        ],
    ]),

]);

$this->params['actions'] = $actions;

echo HintWidget::widget(['message' => '#ClientSiteView.hint']);
echo '<div class="client-site-view">' . PHP_EOL;
echo AlertFlash::widget();

$attributes = [
    'id',
    'title',
    'domain',
    'url:url',
    'description',
    'created_at:datetime',
    'updated_at:datetime',
    [
        'label' => Yii::t('app', 'Widget code'),
        'value' => Html::textarea('widgetCode', $model->widgetCode, [
            'rows' => '7',
            'style' => 'width:100%',
            'readonly' => 1,
        ]),
        'format' => 'raw',
    ],
    [
        'label' => Yii::t('app', 'Install instruction'),
        'format' => 'raw',
        'value' => HintWidget::widget(['message' => '#ClientSite.installInstruction'])
            //. (($w = ModelsHelper::userMasterWarnings($user)) ? '<br><br>' . Html::tag('div', $w, ['class' => 'alert alert-warning']) : ''),
    ],
];
if ($user->isAdmin) {
    $attributes = array_merge(['user.username'], $attributes);
}

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

echo '</div>' . PHP_EOL; // class="client-site-view"
