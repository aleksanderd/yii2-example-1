<?php

use app\helpers\ViewHelper;
use app\models\ClientSite;
use app\models\User;
use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\widgets\grid\GridView;
use flyiing\helpers\UniHelper;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSiteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

$this->title = Yii::t('app', 'Websites');
$this->params['breadcrumbs'][] = Html::icon('client-site') . $this->title;

$this->params['related'] = ViewHelper::uspButtons([
    'user' => $user,
    'items' => ['lines', 'rules', 'pages'],
]);
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => Yii::t('app', 'Add website'),
        'options' => [
            'class' => 'btn btn-primary' . ($user->getClientSites()->count() > 0 ? '' : ' btn-warning-dyn'),
        ],
    ],
]);

echo HintWidget::widget(['message' => '#ClientSiteIndex.hint']);
echo '<div class="client-site-index">' . PHP_EOL;
echo AlertFlash::widget();

// echo $this->render('_search', ['model' => $searchModel]);

$columns = [
    [
        'attribute' => 'id',
        'filterOptions' => ['style' => 'max-width: 40px'],
        'hAlign' => 'right',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => Html::activeTextInput($searchModel, 'itud', [
            'class' => 'form-control',
            'placeholder' => $searchModel->attributeLabels()['itud'],
        ]),
        'template' => '<strong>{title}</strong><br><small>{url}</small><br><small>{description}</small>',
        'attributes' => [
            [
                'attribute' => 'title',
                'content' => function (ClientSite $m) {
                    return Html::a($m->title, ['view', 'id' => $m->id], ['data-pjax' => 0]);
                }
            ],
            'description',
            [
                'attribute' => 'url',
                'content' => function (ClientSite $m) {
                    return Html::a($m->url, $m->url, [
                        'target' => '_blank',
                        'data-pjax' => 0,
                    ]);
                }
            ],
        ],
        'hAlign' => 'left',
    ],
    [
        'class' => \flyiing\grid\DataColumn::className(),
        'filter' => false,
        'attributes' => [
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
            ],
        ],
        'hAlign' => 'center',
    ],


];

if (count($user->subjectUsers) > 1) {
    $columns = array_merge([
        [
            'class' => \app\widgets\grid\UserColumn::className(),
            'filterWidgetOptions' => [
                'data' => ArrayHelper::map($user->subjectUsers, 'id', 'username'),
            ],
            //'width' => '20%',
        ],
    ], $columns, [
        [
            'class' => \flyiing\grid\DataColumn::className(),
            'filterTemplate' => '{w_check_result}',
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'hideSearch' => true,
                'data' => ClientSite::wCodeResultStatuses(),
                'pluginOptions' => [
                    'placeholder' => Yii::t('app', 'Any'),
                    'allowClear' => true,
                ],
            ],
            'attributes' => [
                [
                    'attribute' => 'w_checked_at',
                    'format' => 'datetime',
                ],
                [
                    'attribute' => 'w_changed_at',
                    'format' => 'datetime',
                ],
                [
                    'attribute' => 'w_check_result',
                    'content' => function (ClientSite $m) {
                        return ViewHelper::wCodeResult($m);
                    }
                ],
            ],
            'hAlign' => 'center',
        ],
    ]);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'pjax' => true,
]);

echo '</div>' . PHP_EOL; // class="client-site-index"
