<?php

/* @var $this yii\web\View */
/* @var $lines yii\db\ActiveQuery */

use flyiing\helpers\Html;
use yii\helpers\Url;

echo Html::beginTag('ul', [
    'class' => 'list-group rule-lines',
]);

$css = <<<CSS
.rule-lines {
    margin-bottom: 0;
}
.rule-lines .list-group-item {
    padding-top: 3px;
    padding-bottom: 3px;
}
CSS;

$this->registerCss($css);

foreach ($lines->all() as $line) {
    $content = Html::icon('client-line') . Html::a($line->title, Url::toRoute(['client-line/view', 'id' => $line->id]), ['data-pjax' => 0])
        .' '. Html::tag('small', $line->info);
    echo Html::tag('li', $content, ['class' => 'list-group-item']);
}

echo Html::endTag('ul');