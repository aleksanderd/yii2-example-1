<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\variable\WTextsBase */
/* @var $form app\widgets\ActiveForm */
/** @var app\models\User $user */
$user = Yii::$app->user->identity;

echo '<div class="panel-body">';

echo Html::tag('h2', Yii::t('app', 'Modal widow basic texts'));
echo HintWidget::widget(['message' => '#WTextsBase.basic.hint']) . '<hr/>';

echo $form->field($model, 'rotateModal')->widget(\kartik\select2\Select2::className(), [
    'data' => [
        'static' => Yii::t('app', 'Use static texts'),
        'rotate' => Yii::t('app', 'Rotate texts'),
    ],
    'hideSearch' => true,
]);            if (is_array($rmIds = ArrayHelper::getValue($model, 'rotateModalIds', ''))) {
    $model->rotateModalIds = implode(',', $rmIds);
}

$link = Html::a(Yii::t('app', 'Edit modal texts'), Url::to(['/modal-text/index']));
$label = ArrayHelper::getValue($model->attributeLabels(), 'rotateModalIds') .'<br/>'. $link;
$items = ArrayHelper::map($model->modalTexts, 'id', 'title');
$model->rotateModalIds = explode(',', ArrayHelper::getValue($model, 'rotateModalIds', ''));
echo $form->field($model, 'rotateModalIds')->listBox($items, [
    'multiple' => true,
])->label($label);

echo $form->field($model, 'modalTitle')->input('text');
echo $form->field($model, 'modalClose')->input('text');
echo $form->field($model, 'modalNotice')->textarea(['rows' => 7]);
echo $form->field($model, 'modalInputPlaceholder')->input('text');
echo $form->field($model, 'modalSubmit')->input('text');
echo $form->field($model, 'modalDescription')->textarea(['rows' => 7]);

echo Html::tag('h2', Yii::t('app', 'Modal window statistics texts'));
echo HintWidget::widget(['message' => '#WTextsBase.stats.hint']) . '<hr/>';

echo $form->field($model, 'modalSupShow')->widget(\kartik\select2\Select2::className(), [
    'data' => [
        'none' => Yii::t('app', 'Do not show'),
        'append' => Yii::t('app', 'Append to description'),
        'replace' => Yii::t('app', 'Replace description'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'modalSupAvail')->input('text');
echo $form->field($model, 'modalSupBusy')->input('text');
echo $form->field($model, 'modalSupHelped')->input('text');

echo Html::tag('h2', Yii::t('app', 'Modal window status texts'));
echo HintWidget::widget(['message' => '#WTextsBase.status.hint']) . '<hr/>';

echo $form->field($model, 'modalStatusInit')->input('text');
echo $form->field($model, 'modalStatusInputTip')->input('text');
echo $form->field($model, 'modalStatusInputOk')->input('text');
echo $form->field($model, 'modalStatusRequest')->input('text');
echo $form->field($model, 'modalStatusCallMan')->input('text');
echo $form->field($model, 'modalStatusZeroDef')->input('text');

echo Html::tag('h2', Yii::t('app', 'Deferred calls texts'));
echo HintWidget::widget(['message' => '#WTextsBase.deferred.hint']) . '<hr/>';

echo $form->field($model, 'modalWorkTimeSelect')->input('text');
echo $form->field($model, 'modalNoRule')->textarea(['rows' => 7]);
echo $form->field($model, 'modalWTSubmit')->input('text');
echo $form->field($model, 'modalWTSubmitted')->textarea(['rows' => 7]);


echo Html::tag('h2', Yii::t('app', 'Other texts'));
echo HintWidget::widget(['message' => '#WTextsBase.other.hint']) . '<hr/>';

echo $form->field($model, 'error')->input('text');
echo $form->field($model, 'success')->input('text');

echo '</div>'; // panel-body

