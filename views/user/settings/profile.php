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
 * @var yii\web\View $this
 * @var app\widgets\ActiveForm $form
 * @var dektrium\user\models\Profile $profile
 */

$this->title = Yii::t('user', 'Profile settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-lg-12">
        <?= $this->render('_menu') . '<hr/>' ?>
    </div>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'profile-form',
                    'enableAjaxValidation'   => false,
                    'enableClientValidation' => false,
                    'validateOnBlur'         => false,
                ]);

                echo $form->field($model, 'name');
                echo $form->field($model, 'public_email');
                echo $form->field($model, 'website');
                echo $form->field($model, 'phone');
                echo $form->field($model, 'company');
                echo $form->field($model, 'location');
                echo $form->field($model, 'gravatar_email')->hint(\yii\helpers\Html::a(Yii::t('user', 'Change your avatar at Gravatar.com'), 'http://gravatar.com'));
                echo $form->field($model, 'bio')->textarea();

                echo $form->buttons();
                ActiveForm::end();

                ?>
            </div>
        </div>
    </div>
</div>
