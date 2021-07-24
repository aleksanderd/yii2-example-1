<?php

use flyiing\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $keyModel yii\base\DynamicModel */
/* @var $name string */

$this->title = Yii::t('var', $model->name) .' ('. Yii::t('app', 'Variable form') .')';
// TODO сделать индекс форм
//$this->params['breadcrumbs'][] = [
//    'label' => Html::icon('variable') . Yii::t('app', 'Variables'),
//    'url' => ['index']
//];
$this->params['breadcrumbs'][] = Html::icon('model-update') . Yii::t('var', $model->name);

\kartik\checkbox\CheckboxXAsset::register($this);
\app\widgets\vw\VariableWidgetAsset::register($this);

echo '<div class="variable-form">' . PHP_EOL;
echo $this->render('_variable-form', compact('model', 'name', 'keyModel'));
echo '</div>' . PHP_EOL;
