<?php

use flyiing\helpers\Html;
use yii\bootstrap\Button;
use app\models\ClientQuery;
use app\helpers\DataHelper;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model ClientQuery */

$css = <<<CSS

table.record * {
    vertical-align: middle;
}

.record-player {
    width: 200px;
    text-align: left;
}
CSS;
$this->registerCss($css);

$js = <<<JS

function gmcfPlayRecord(el, url)
{
    var audio = document.createElement('audio');
    audio.controls = 'controls';
    //audio.preload = 'auto';
    audio.autoplay = 'autoplay';
    audio.className = 'record-player';
    audio.innerHTML = '<source src="' + url + '" />';
    el.parentNode.replaceChild(audio, el);
}

JS;
$this->registerJs($js, View::POS_HEAD);

if (isset($model->record['url'])) {
    $url = $model->record['url'];
    if ($model->record_time > 0) {
        $title = DataHelper::durationToText($model->record_time);
    } else {
        $title = Yii::t('app', 'Play');
    }

    $btnPlay = [
        'label' => Html::icon('play') .' '. $title,
        'encodeLabel' => false,
        'options' => [
            'class' => 'btn btn-sm btn-success record-player',
            'onclick' => "gmcfPlayRecord(this, '$url');",
        ],
    ];
    $btnDownload = [
        'tagName' => 'a',
        'label' => Html::icon('download'),
        'encodeLabel' => false,
        'options' => [
            'href' => $url,
            'download' => 'record-'. $model->id .'.mp3',
            'target' => '_blank',
            'class' => 'btn btn-sm btn-success',
        ],
    ];

    echo Button::widget($btnPlay);
    echo '<br/>';
    echo Html::tag('small', Html::a(Html::icon('download') . Yii::t('app', 'Download'), $model->record['url'], [
        'data-pjax' => 0,
        'target' => '_blank',
    ]));

//    $tds = Html::tag('td', Button::widget($btnPlay));
//    $tds .= Html::tag('td', Button::widget($btnDownload));
//    $tds .= Html::tag('td', Button::widget($btnDownload));
//    $tr = Html::tag('tr', $tds);
//    echo Html::tag('table', $tr, ['class' => 'record']);

} else {
    echo $this->render('_status_label', compact('model'));
}
