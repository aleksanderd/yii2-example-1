<?php

namespace app\widgets\grid;

use Yii;
use app\helpers\DataHelper;
use flyiing\helpers\Html;
use kartik\grid\DataColumn;
use yii\helpers\ArrayHelper;
use app\models\ClientQueryCall;

class CallResultColumn extends DataColumn {

    public function __construct($config = [])
    {
        parent::__construct(ArrayHelper::merge([
            'label' => Yii::t('app', 'Result'),
            'content' => function (ClientQueryCall $m) {
                if ($value = $m->connected_at) {
                    $class = 'call-connected';
                    $content = Html::icon('check') . Yii::$app->formatter->format($m->connected_at, 'time') .' - '.
                        Yii::$app->formatter->format($m->disconnected_at, 'time') .' <br> '.
                        Html::icon('angle-right') . DataHelper::durationToText($value - $m->started_at) .
                        Html::icon('angle-right') . DataHelper::durationToText($m->duration);
                } else if ($value = $m->failed_at) {
                    $class = 'call-failed';
                    $content = Html::icon('remove') . Yii::$app->formatter->format($m->failed_at, 'time') .'<br>'.
                        Html::icon('angle-right') . DataHelper::durationToText($value - $m->started_at);
                } else {
                    return '-';
                }
                //$content .= '<br>' . DataHelper::durationToText($value - $m->started_at);
                return Html::tag('div', $content, ['class' => $class]);
            },
        ], $config));
    }

}
