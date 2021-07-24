<?php

use app\themes\inspinia\widgets\IBoxWidget;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $queries app\models\ClientQuery[] */

$this->registerCssFile('@web/css/last-queries.css');

$content = '';
$pDate = '';
foreach ($queries as $query) {
    $date = Yii::$app->formatter->format($query->at, 'date');
    if ($date != $pDate) {
        $content .= Html::tag('tr', Html::tag('td', $date, [
            'colspan' => 11,
            'class' => 'last-queries-date'
        ]));
        $pDate = $date;
    }
    $content .= $this->render('_last_item', ['model' => $query]);
}


$title = Yii::t('app', 'Recent queries');
$ths = Html::tag('th', Yii::t('app', 'ID'), ['style' => 'text-align: right']) .
    Html::tag('th', Yii::t('app', 'Time'), ['style' => 'text-align: center']) .
    Html::tag('th', Yii::t('app', 'Website')) .
    Html::tag('th', Yii::t('app', 'Call info'), ['style' => 'text-align: center']) .
    Html::tag('th', Yii::t('app', 'Record'));
$header = Html::tag('tr', $ths);
$header = Html::tag('thead', $header);
$content = Html::tag('tbody', $content);
$content = Html::tag('table', $header . $content, [
    'class' => 'table table-hover table-condensed last-queries',
]);

echo IBoxWidget::widget(compact('title', 'content'));
