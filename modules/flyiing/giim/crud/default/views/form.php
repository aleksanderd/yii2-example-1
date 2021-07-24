<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelBasename = Inflector::camel2words(StringHelper::basename($generator->modelClass));
$modelId = Inflector::camel2id(StringHelper::basename($generator->modelClass));

echo "<?php\n";
?>

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];

if($model->isNewRecord) {
    $this->title = <?= $generator->generateString('Add ' . strtolower($modelBasename)) ?>;
    $this->params['breadcrumbs'][] = Html::icon('model-create') . <?= $generator->generateString('Add'); ?>;
    echo '<div class="crud-create <?= $modelId ?>-create">' . PHP_EOL;
} else {
    $this->title = <?= $generator->generateString('Update ' . strtolower($modelBasename)) ?> . ': ' . $model->title;
    $this->params['breadcrumbs'][] = Html::icon('model-update') . <?= $generator->generateString('Update'); ?>;
    echo '<div class="crud-update <?= $modelId ?>-update">' . PHP_EOL;
}

echo AlertFlash::widget();

echo $this->render('_form', [
    'model' => $model,
]);

echo '</div>' . PHP_EOL;
