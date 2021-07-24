<?php

use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $last app\models\stats\QueriesStats */
/* @var $prev app\models\stats\QueriesStats */
/* @var $labels array|null */

global $mLast, $mPrev;
$mLast = $last;
$mPrev = isset($prev) ? $prev : null;
if (!isset($title)) {
    $title = '';
}

require_once('_func.php');

$labels = ArrayHelper::merge([
    'prev' => Yii::t('app', 'Previous'),
    'last' => Yii::t('app', 'Last'),
    'changes' => Yii::t('app', 'Progress'),
], isset($labels) ? $labels : []);

echo '<table class="table table-hover no-margins">';
if (isset($mPrev)) {
    echo '<thead>';
    echo Html::tag('th', '&nbsp;');
    foreach ($labels as $l) {
        echo Html::tag('th', $l);
    }
    echo '</thead>';
}
echo '<tbody>';
echo renderItem('hits');
echo renderItem('visits');
echo renderItem('visits_unique');
echo renderItem('total');
echo renderItem('success');
echo renderItem('successPct', '%');
echo renderItem('failMgr');
echo renderItem('clientCost');
echo renderItem('recordTime', Yii::t('app', 's.'));
echo '</tbody>';
echo '</table>';
