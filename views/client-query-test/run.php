<?php

use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ClientQueryTest */
/* @var $rule app\models\ClientRule */
/* @var $query app\models\ClientQuery */
/* @var $debug array */

$this->title = Yii::t('app', 'Run') . ': ' . $model->title;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('client-query-test') . Yii::t('app', 'Query tests'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Html::icon('run') . Yii::t('app', 'Run');
$this->params['actions'] = UniHelper::getModelActions($model, ['view', 'update']);

echo '<div class="client-query-test-run">' . PHP_EOL;

echo AlertFlash::widget();

echo Html::tag('h1', 'Run: <b>' . $model->title . '</b>');
if ($rule) {
    echo Html::tag('h2', 'Result rule: <b>' . $rule->title . '</b>');
}

if (is_array($debug)) {

    echo '<hr>';
    echo ArrayHelper::remove($debug, 'sql') . '<br><hr>';
    if ($rules = ArrayHelper::remove($debug, 'rules')) {
        foreach ($rules as $ruleItem) {
            $tRule = $ruleItem['rule'];
            echo Html::tag('h4', 'Rule test: ' . $tRule->title);
            echo Html::tag('pre', print_r($ruleItem['debug'], true));
        }
    }
    unset($debug['result']);

    if ($dbg = ArrayHelper::remove($debug, 'process')) {
        echo Html::tag('h2', 'Process info');
        echo ArrayHelper::remove($dbg, 'sql') . '<br><hr>';
        echo Html::tag('h4', 'Lines:') . Html::tag('pre', print_r(ArrayHelper::remove($dbg, 'lines'), true));

        if ($vi = ArrayHelper::remove($dbg, 'vi')) {
            foreach ($vi as $k => $v) {
                echo Html::tag('h4', $k . ':');
                echo Html::tag('pre', print_r($v, true));
            }
        }

        if (sizeof($dbg) > 0) {
            echo Html::tag('h3', 'Other process debug info');
            echo Html::tag('pre', print_r($dbg, true));
        }

    }

    if (sizeof($debug) > 0) {
        echo Html::tag('h3', 'Other debug info');
        echo Html::tag('pre', print_r($debug, true));
    }
}

echo '</div>' . PHP_EOL; // class="client-query-run-view"
