<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\bootstrap\Tabs;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\VariableModel */
/* @var $form app\widgets\ActiveForm */
/* @var $options array */

$this->params['wrapperClass'] = 'gray-bg';

if (!isset($options)) {
    $options = [];
}

$params = compact('model', 'form');

$ofClass = $form->fieldClass;
if ($model->user_id !== null && $model->user_id > 0) {
    $form->fieldClass = \app\widgets\VariableField::className();
}

$contact = HintWidget::widget(['message' => '#UNotify.contacts.hint']) . '<br><br>';
$contact .= $form->field($model, 'emailFrom')->input('text');
$contact .= $form->field($model, 'copyTo')->input('text');
$contact .= $form->field($model, 'emailTo')->input('text');
$contact .= $form->field($model, 'smsTo')->input('text');
$items = [
    [
        'label' => Yii::t('app', 'Contact information'),
        'content' => $contact,
    ],
];
$content = $this->render('_notify', array_merge($params, ['prefix' => 'userNew']));
if (strlen($content) > 0) {
    $items[] = [
        'label' => Yii::t('app', 'New user notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.userNew.hint']) . '<br><br>' . $content,
    ];
}
$content = $this->render('_notify', array_merge($params, ['prefix' => 'siteNew']));
if (strlen($content) > 0) {
    $items[] = [
        'label' => Yii::t('app', 'New website notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.siteNew.hint']) . '<br><br>' . $content,
    ];
}
$content = $this->render('_notify', array_merge($params, ['prefix' => 'siteNewInactive']));
if (strlen($content) > 0) {
    $items[] = [
        'label' => Yii::t('app', 'New inactive website notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.siteNewInactive.hint']) . '<br><br>' . $content,
    ];
}

$items = array_merge($items, [
    [
        'label' => Yii::t('app', 'Success query notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.querySuccess.hint']) . '<br><br>'
            . $this->render('_notify', array_merge($params, ['prefix' => 'querySuccess'])),
    ],
    [
        'label' => Yii::t('app', 'Failed query notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.queryFail.hint']) . '<br><br>'
            . $this->render('_notify', array_merge($params, ['prefix' => 'queryFail'])),
    ],
]);

//$content = $this->render('_notify', array_merge($params, ['prefix' => 'queryUnpaid']));
//if (strlen($content) > 0) {
//    $items[] = [
//        'label' => Yii::t('app', 'Unpaid call query notification'),
//        'content' => HintWidget::widget(['message' => '#UNotify.queryUnpaid.hint']) . '<br><br>' . $content,
//    ];
//}

if (!ArrayHelper::getValue($options, 'disableFin', false)) {
    $content = Html::tag('h2', Yii::t('app', 'Balance and payments notifications'));
    $content .= HintWidget::widget(['message' => '#UNotify.balancePayments.hint']) . '<hr/>';
    $content .= $form->field($model, 'minBalanceValue')->widget(\kartik\money\MaskMoney::className());
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'minBalance']));
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'paymentNew']));

    $content .= Html::tag('h2', Yii::t('app', 'Tariffs notifications'));
    $content .= HintWidget::widget(['message' => '#UNotify.tariffs.hint']) . '<hr/>';
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'tariffEnd']));

    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'tariffRenewFail']));

    //$content .= Html::tag('h2', Yii::t('app', 'Unpaid queries notifications'));
    //$content .= HintWidget::widget(['message' => '#UNotify.queryUnpaid.hint']) . '<hr/>';
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'queryUnpaid']));

    $content .= Html::tag('h2', Yii::t('app', 'Payouts notifications'));
    $content .= HintWidget::widget(['message' => '#UNotify.payouts.hint']) . '<hr/>';
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'payoutComplete']));
    $content .= $this->render('_notify', array_merge($params, ['prefix' => 'payoutRejected']));
    $items = array_merge($items, [
        [
            'label' => Yii::t('app', 'Financial notifications'),
            'content' => HintWidget::widget(['message' => '#UNotify.finance.hint']) . '<br><br>' . $content,
        ],
    ]);
}

$items = array_merge($items, [
    [
        'label' => Yii::t('app', 'Support replied notification'),
        'content' => HintWidget::widget(['message' => '#UNotify.supportReplied.hint']) . '<br><br>'
            . $this->render('_notify', array_merge($params, ['prefix' => 'supportReplied'])),
    ],
]);

foreach ($items as $k => $item) {
    $items[$k]['content'] = Html::tag('div', $item['content'], ['class' => 'panel-body']);
}

$tabs = Tabs::widget([
//    'navType' => 'nav-pills',
    'items' => $items,
]);

echo Html::tag('div', $tabs, ['class' => 'tabs-container']);

echo '<br>';

$form->fieldClass = $ofClass;
