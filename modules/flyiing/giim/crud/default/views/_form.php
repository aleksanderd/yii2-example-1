<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}
$modelBasename = Inflector::camel2id(StringHelper::basename($generator->modelClass));
$formName = $modelBasename . '-form';
echo "<?php\n";
?>

use flyiing\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$form = ActiveForm::begin([
    'id' => '<?= $formName ?>',
    'enableAjaxValidation' => true,
]);

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        echo 'echo ' . $generator->generateActiveField($attribute) . ';' . PHP_EOL;
    }
} ?>

echo $form->buttons();

ActiveForm::end();
