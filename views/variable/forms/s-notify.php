<?php

use app\widgets\hint\HintWidget;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */

$params = compact('model', 'form');

$contacts = $form->field($model, 'emailFrom')->input('text');
$contacts .= $form->field($model, 'smsFrom')->input('text');
$contacts .= $form->field($model, 'emailTo')->input('text');
$contacts .= $form->field($model, 'smsTo')->input('text');

echo Tabs::widget([
    'navType' => 'nav-pills',
    'items' => [
        [
            'label' => Yii::t('app', 'Contact information'),
            'content' => HintWidget::widget(['message' => '#SNotify.contacts.hint']) .'<br><br>'. $contacts,
        ],
        [
            'label' => Yii::t('app', 'New user notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.userNew.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'userNew'])),
        ],
        [
            'label' => Yii::t('app', 'New website notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.siteNew.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'siteNew'])),
        ],
        [
            'label' => Yii::t('app', 'New partner notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.partnerNew.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'partnerNew'])),
        ],
        [
            'label' => Yii::t('app', 'Payout request notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.payoutRequest.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'payoutRequest'])),
        ],
        [
            'label' => Yii::t('app', 'Widget removed notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.widgetRemoved.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'widgetRemoved'])),
        ],
        [
            'label' => Yii::t('app', 'Support request notification'),
            'content' => HintWidget::widget(['message' => '#SNotify.supportRequest.hint']) . '<br><br>'
                . $this->render('_notify', array_merge($params, ['prefix' => 'supportRequest'])),
        ],
    ],
]);

echo '<br>';
