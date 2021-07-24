<?php

namespace app\widgets\grid;

use flyiing\helpers\Html;
use Yii;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;

class SiteColumn extends DataColumn {

    public function __construct($config = [])
    {
        parent::__construct(ArrayHelper::merge([
            'attribute' => 'site_id',
            'label' => Yii::t('app', 'Website'),
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'pluginOptions' => [
                    'placeholder' => Yii::t('app', 'Website'),
                    'allowClear' => true,
                ],
            ],
            'content' => function($model) {
                if ($site = $model->site) {
                    /** @var \app\models\ClientSite $site */
                    $url = Html::a($site->url, $site->url, [
                        'target' => '_blank',
                        'data-pjax' => 0,
                    ]);
                    $title = Html::a($site->title, ['//client-site/view', 'id' => $site->id], [
                        'target' => '_blank',
                        'data-pjax' => 0,
                    ]);
                    return Html::tag('small', '#' . $site->id) .' '. Html::tag('strong', $title) .'<br/>'. Html::tag('small', $url);
                } else {
                    return '-';
                }
            },
        ], $config));
    }

}
