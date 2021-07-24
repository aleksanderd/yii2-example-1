<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use flyiing\helpers\Html;
use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchModelClass, '\\') ?> */

echo '<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">' . PHP_EOL;

$form = ActiveForm::begin([
    'action' => ['index'],
        'method' => 'get',
]);

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        echo 'echo ' . $generator->generateActiveSearchField($attribute) . ';' . PHP_EOL;
    } else {
        echo '//echo ' . $generator->generateActiveSearchField($attribute) . ';' . PHP_EOL;
    }
}
?>

echo $form->buttons([
    'submit' => ['label' => <?= $generator->generateString('Search') ?>],
    'reset',
]);

ActiveForm::end();

echo '</div>' . PHP_EOL; // class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search"
