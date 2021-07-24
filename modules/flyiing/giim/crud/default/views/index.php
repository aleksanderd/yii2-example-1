<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$modelBasename = Inflector::camel2words(StringHelper::basename($generator->modelClass));
$modelId = Inflector::camel2id(StringHelper::basename($generator->modelClass));

echo "<?php\n";
?>

use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use flyiing\helpers\UniHelper;
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize($modelBasename)) ?>;
$this->params['breadcrumbs'][] = Html::icon('<?= $modelId ?>') . $this->title;
$this->params['actions'] = UniHelper::getModelActions([
    'create' => [
        'label' => <?php echo $generator->generateString('Add ' . strtolower($modelBasename)); ?>,
    ]
]);

echo '<div class="<?= $modelId ?>-index">' . PHP_EOL;

echo AlertFlash::widget();

<?php if(!empty($generator->searchModelClass)): ?>
<?= ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]);
<?php endif; ?>

<?php if ($generator->indexWidgetType === 'grid'): ?>
echo GridView::widget([
    'dataProvider' => $dataProvider,
    <?= !empty($generator->searchModelClass) ? "//'filterModel' => \$searchModel,\n    'columns' => [\n" : "'columns' => [\n"; ?>
        ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "        '" . $name . "',\n";
        } else {
            echo "        // '" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (++$count < 6) {
            echo "        '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        } else {
            echo "        // '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

        ['class' => 'flyiing\grid\ActionColumn'],
    ],
]);
<?php else: ?>
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'itemView' => function ($model, $key, $index, $widget) {
        return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
    },
]);
<?php endif; ?>

echo '</div>' . PHP_EOL; // class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index"
