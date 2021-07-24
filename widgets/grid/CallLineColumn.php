<?php

namespace app\widgets\grid;

use Yii;
use flyiing\helpers\Html;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;
use app\models\ClientQueryCall;

class CallLineColumn extends DataColumn {

    public function __construct($config = [])
    {
        parent::__construct(ArrayHelper::merge([
            'label' => Yii::t('app', 'Info') .' / '. Yii::t('app', 'Phone line'),
            'content' => function (ClientQueryCall $m) {
                $content = $m->info;
                if ($content == $m->query->call_info) {
                    $content = $m->query->callInfo;
                }
                /** @var \app\models\ClientLine $line */
                if ($line = $m->line) {
                    $content .= '<br>' . Html::icon('client-line') .
                        Html::a($line->title, ['/client-line/view', 'id' => $line->id]);
                } else {
                    $content = Html::tag('b', $content);
                }
                return $content;
            },
            'hAlign' => 'center',
        ], $config));
    }

}
