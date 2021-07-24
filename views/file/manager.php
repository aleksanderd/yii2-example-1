<?php

use app\helpers\DataHelper;
use app\widgets\ActiveForm;
use flyiing\helpers\Html;
use yii\bootstrap\Button;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $files string[] */
/* @var $baseUrl string */

$this->title = Yii::t('app', 'Files');
$this->params['breadcrumbs'][] = Html::icon('file') . $this->title;

$form = ActiveForm::begin([
    'id' => 'file-manager-uploader',
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
    //'enableAjaxValidation' => true,
]);

echo \flyiing\widgets\AlertFlash::widget();

echo \kartik\file\FileInput::widget([
    'id' => 'files2upload',
    'name' => 'files2upload[]',
    'options' => [
        //'uploadUrl' => Url::to(['/file/upload']),
        'multiple' => true,
        'accept' => 'image/*, audio/mpeg, audio/ogg',
    ],
]);

//echo Html::fileInput('files2upload');
//echo $form->buttons();

ActiveForm::end();

$css =<<<CSS

.files .file-size {
    font-style: italic;
    font-size: small;
    font-weight: lighter;
    text-align: right;
}

.files .file-name {
    font-size: 120%;
    font-weight: bold;
}

.files .file-preview {
    width: 220px;
    text-align: center;
    vertical-align: middle;
    margin: auto;
}

.files .file-preview img {
    max-height: 45px;
}

.files .file-preview audio {
    max-width: 200px;
}

.files .file-ops {
    text-align: center;
    vertical-align: middle;
}

CSS;
$this->registerCss($css);

echo '<hr>';

if (count($files) < 1) {
    echo Yii::t('app', 'There is no any files yet. Please, use upload form above to add some files.');
    return;
}

echo '<table class="table table-bordered table-hover files">';
//$head = Html::tag('th', Yii::t('app', 'Size'), ['class' => 'file-size'])
//    . Html::tag('th', Yii::t('app', 'File name'), ['class' => 'file-name'])
//    . Html::tag('th', Yii::t('app', 'Preview'), ['class' => 'file-preview'])
//    . Html::tag('th', '&nbsp;');
//echo Html::tag('thead', Html::tag('tr', $head));

foreach ($files as $file) {
    echo '<tr>';
//    echo Html::tag('td', $file['size'], ['class' => 'file-size']);
//    echo Html::tag('td', $file['name'], ['class' => 'file-name']);

    $size = Html::tag('span', DataHelper::formatBytes($file['size']), ['class' => 'file-size']);
    echo Html::tag('td', $file['name'] .'<br>'. $size, ['class' => 'file-name']);

    $fileUrl = $baseUrl . '/' . $file['name'];
    $t = explode('/', $file['mime']);
    if ($t[0] == 'image') {
        $preview = Html::img($fileUrl, ['class' => 'img-rounded']);
    } else if ($t[0] == 'audio') {
        $src = Html::tag('source', '', [
            'type' => $file['mime'],
            'src' => $fileUrl,
        ]);
        $preview = Html::tag('audio', $src, ['controls' => '']);
        $preview = Html::tag('audio', $src, [
            'src' => $fileUrl,
            'controls' => 'controls',
            'preload' => 'metadata',
        ]);

    } else {
        $preview = '&nbsp;';
    }
    echo Html::tag('td', $preview, ['class' => 'file-preview']);

    $btnDelete = Button::widget([
        'tagName' => 'a',
        'label' => Html::icon('trash') . Yii::t('app', 'Delete'),
        'encodeLabel' => false,
        'options' => [
            'class' => 'btn-xs btn-danger',
            'href' => Url::to(['/file/delete', 'name' => $file['name']]),
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete file "{filename}"?',
                    ['filename' => $file['name']]),
                'method' => 'post',
                'pjax' => 0,
            ],
        ]
    ]);
    echo Html::tag('td', $btnDelete, ['class' => 'file-ops']);

    echo '</tr>';
}

echo '</table>';
