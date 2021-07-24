<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use app\widgets\ActiveForm;

/**
 * @var $this  yii\web\View
 * @var $form  app\widgets\ActiveForm
 * @var $model dektrium\user\models\SettingsForm
 */

$this->title = Yii::t('user', 'Account settings');
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
                    'id'          => 'account-form',
                    'enableAjaxValidation'   => false,
                    'enableClientValidation' => false,
                ]);

                echo $form->field($model, 'email');
                echo $form->field($model, 'username');
                echo $form->field($model, 'new_password')->passwordInput();
                echo '<hr/>';
                echo $form->field($model, 'current_password')->passwordInput();

                echo $form->buttons();
                ActiveForm::end();

                ?>

            </div>
        </div>
    </div>
</div>
