<?php

use app\helpers\DataHelper;
use app\helpers\ViewHelper;
use app\widgets\hint\HintWidget;
use app\widgets\slider\IonSlider;
use flyiing\helpers\Html;
use kartik\color\ColorInput;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\variable\WModalOptions */
/* @var $form app\widgets\ActiveForm */

$opacityOpts = [
    'pluginOptions' => [
        'min' => 0,
        'max' => 1,
        'step' => 0.01,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
];
$colorOpts = [
    'pluginOptions' => [
        'preferredFormat' => false,
    ],
];
$otherImages = [
    'none' => Yii::t('app', 'No image'),
    'custom' => Yii::t('app', 'Custom image url'),
];

$userImages = $model->user ? DataHelper::filesSelectData('/public/' . $model->user->getFilesPath(), '/image.*/') : [];

echo '<div class="panel-body">';
echo HintWidget::widget(['message' => '#WModalOptions.hint']) . '<hr/>';

/**
 * Основные настройки
 */

echo Html::tag('h2', Yii::t('app', 'General window settings'));
echo HintWidget::widget(['message' => '#WMGeneral.hint']) . '<hr/>';

echo $form->field($model, 'style')->widget(Select2::className(), [
    'data' => [
        'classic' => Yii::t('app', 'Classic'),
        'compact' => Yii::t('app', 'Compact'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'position')->widget(Select2::className(), [
    'data' => [
        'left' => Yii::t('app', 'Left'),
        'center' => Yii::t('app', 'Center'),
        'right' => Yii::t('app', 'Right'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'animation')->widget(Select2::className(), [
    'data' => ViewHelper::getIOAnimations(),
    'hideSearch' => true,
]);
echo $form->field($model, 'blockPage')->widget(Select2::className(), [
    'data' => [
        10 => Yii::t('app', 'Page accessible'),
        0 => Yii::t('app', 'Page blocked'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'darkenColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'borderRadius')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 33,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);
echo $form->field($model, 'shadowSize')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 30,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);
echo $form->field($model, 'shadowBlur')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);
echo $form->field($model, 'shadowColor')->widget(ColorInput::className(), $colorOpts);

/**
 * Цвета и фоновое изображение
 */

echo Html::tag('h2', Yii::t('app', 'Colors and background image'));
echo HintWidget::widget(['message' => '#WMColorsAndBackground.hint']) . '<hr/>';

echo $form->field($model, 'opacity')->widget(IonSlider::className(), $opacityOpts);
echo $form->field($model, 'bgColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'color')->widget(ColorInput::className(), $colorOpts);
$data = [
    Yii::t('app', 'Other background') => $otherImages,
    Yii::t('app', 'System backgrounds') => DataHelper::filesSelectData('/cli/img/backgrounds', '/image.*/'),
];
if (count($userImages) > 0) {
    $data[Yii::t('app', 'User images')] = $userImages;
}
echo $form->field($model, 'bgImage')->widget(Select2::className(), [
    'data' => $data,
    'hideSearch' => true,
]);
echo $form->field($model, 'bgImageUrl')->input('text');
echo $form->field($model, 'bgImageRepeat')->widget(Select2::className(), [
    'data' => [
        'no-repeat' => Yii::t('app', 'No repeat / cover'),
        'repeat' => Yii::t('app', 'Repeat'),
        'repeat-x' => Yii::t('app', 'Repeat X'),
        'repeat-y' => Yii::t('app', 'Repeat Y'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'bgImageOpacity')->widget(IonSlider::className(), $opacityOpts);
echo $form->field($model, 'cbBgColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'cbColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'timerBgColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'timerColor')->widget(ColorInput::className(), $colorOpts);

/**
 * Логотип
 */

echo Html::tag('h2', Yii::t('app', 'Logo image'));
echo HintWidget::widget(['message' => '#WMLogoImage.hint']) . '<hr/>';

$data = [
    Yii::t('app', 'Other logo') => $otherImages,
    Yii::t('app', 'System backgrounds') => DataHelper::filesSelectData('/cli/img/logos', '/image.*/'),
];
if (count($userImages) > 0) {
    $data[Yii::t('app', 'User images')] = $userImages;
}
echo $form->field($model, 'logoImage')->widget(Select2::className(), [
    'data' => $data,
    'hideSearch' => true,
]);
echo $form->field($model, 'logoImageUrl')->input('text');
echo $form->field($model, 'logoImageOpacity')->widget(IonSlider::className(), $opacityOpts);

/**
 * Элементы формы
 */

echo Html::tag('h2', Yii::t('app', 'Form elements'));
echo HintWidget::widget(['message' => '#WMFormElements.hint']) . '<hr/>';

echo $form->field($model, 'prefixSelector')->widget(Select2::className(), [
    'data' => [
        1 => Yii::t('app', 'Show prefix selector'),
        0 => Yii::t('app', 'Hide prefix selector'),
    ],
    'hideSearch' => true,
]);
echo $form->field($model, 'inputRadius')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 22,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);
echo $form->field($model, 'inputBgColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'inputColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'buttonBgColor')->widget(ColorInput::className(), $colorOpts);
echo $form->field($model, 'buttonColor')->widget(ColorInput::className(), $colorOpts);

/**
 * Дополнительные настройки
 */

echo Html::tag('h2', Yii::t('app', 'Advanced settings'));
echo HintWidget::widget(['message' => '#WMAdvancedSettings.hint']) . '<hr/>';

echo $form->field($model, 'invert')->widget(IonSlider::className(), [
    'pluginOptions' => [
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'hide_min_max' => true,
        'hide_from_to' => true,
        'grid' => false,
    ],
]);
echo $form->field($model, 'customCss')->textarea(['rows' => 7]);

echo '</div>';