<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$modelId = Inflector::camel2id(StringHelper::basename($generator->modelClass));

echo "<?php\n";
?>

use yii\widgets\DetailView;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = [
    'label' => Html::icon('<?= $modelId ?>') . <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>,
    'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
$this->params['actions'] = UniHelper::getModelActions($model, ['update', 'delete']);

echo '<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">' . PHP_EOL;

echo AlertFlash::widget();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "        '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        echo "        '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
    ],
]);

echo '</div>' . PHP_EOL; // class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view"
