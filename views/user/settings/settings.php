<?php

use yii\helpers\Html;
use app\widgets\ActiveForm;
//use flyiing\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\widgets\ActiveForm $form
 * @var yii\base\Model $model
 */

$this->title = Yii::t('app', 'User settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('_menu') . '<hr/>' ?>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'fieldClass' => \app\widgets\VariableField::className(),
                    'id' => 'locale-form',
                    'enableAjaxValidation'   => false,
                    'enableClientValidation' => false,
                    'validateOnBlur'         => false,
                ]); ?>

                <?php
                echo $this->render('//variable/forms/u-settings', compact('form', 'model'));
                /*
                echo $form->field($model, 'language')->widget(\app\widgets\SelectLanguage::className());
                echo $form->field($model, 'timezone')->widget(\app\widgets\SelectTimezone::className());
                $data = ['none' => Yii::t('app', 'None')];
                foreach (ArrayHelper::getValue(Yii::$app->params, 'cssAnimations', []) as $value => $label) {
                    if (is_integer($value)) {
                        $value = $label;
                    }
                    $data[$value] = $label;
                }
                echo $form->field($model, 'pageAnimation')->widget(Select2::className(), [
                    'hideSearch' => true,
                    'data' => $data,
                ]);
                */

                echo $form->buttons();
                ActiveForm::end();
                ?>
            </div>
        </div>
    </div>
</div>
