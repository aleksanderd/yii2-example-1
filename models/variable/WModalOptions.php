<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

class WModalOptions extends VariableModel {

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'w.options.modalOptions';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'style',
            'position',
            'prefixSelector',
            'animation',
            'blockPage',
            'bgColor',
            'bgImage',
            'bgImageUrl',
            'bgImageRepeat',
            'bgImageOpacity',
            'logoImage',
            'logoImageUrl',
            'logoImageOpacity',
            'color',
            'borderRadius',
            'inputRadius',
            'invert',
            'opacity',
            'customCss',
            'inputColor',
            'inputBgColor',
            'buttonColor',
            'buttonBgColor',
            'cbColor',
            'cbBgColor',
            'timerColor',
            'timerBgColor',
            'darkenColor',
            'shadowSize',
            'shadowBlur',
            'shadowColor',
        ]);

        parent::__construct($config);

        $this->addRule([
            'style',
            'position',
            'animation',
            'bgColor',
            'bgImage',
            'bgImageUrl',
            'bgImageRepeat',
            'logoImage',
            'logoImageUrl',
            'color',
            'customCss',
            'inputColor',
            'inputBgColor',
            'buttonColor',
            'buttonBgColor',
            'cbColor',
            'cbBgColor',
            'timerColor',
            'timerBgColor',
            'darkenColor',
            'shadowSize',
            'shadowBlur',
            'shadowColor',
        ], 'string');
        $this->addRule('borderRadius', 'integer', ['min' => 0, 'max' => 100]);
        $this->addRule('inputRadius', 'integer', ['min' => 0, 'max' => 33]);
        $this->addRule('invert', 'integer', ['min' => 0, 'max' => 100]);
        $this->addRule(['bgImageOpacity', 'logoImageOpacity', 'opacity'], 'number', ['min' => 0, 'max' => 1]);
        $this->addRule(['blockPage'], 'safe');
        $this->addRule('prefixSelector', 'number');

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'style' => Yii::t('app', 'Window style'),
            'position' => Yii::t('app', 'Window position'),
            'prefixSelector' => Yii::t('app', 'Prefix selector'),
            'animation' => Yii::t('app', 'Appear animation'),
            'blockPage' => Yii::t('app', 'Block page under modal'),
            'bgColor' => Yii::t('app', 'Background color'),
            'bgImage' => Yii::t('app', 'Background image'),
            'bgImageUrl' => Yii::t('app', 'Custom background url'),
            'bgImageRepeat' => Yii::t('app', 'Background image repeat'),
            'bgImageOpacity' => Yii::t('app', 'Background image opacity'),
            'logoImage' => Yii::t('app', 'Logo image'),
            'logoImageUrl' => Yii::t('app', 'Custom logo url'),
            'logoImageOpacity' => Yii::t('app', 'Logo image opacity'),
            'color' => Yii::t('app', 'Ink color'),
            'invert' => Yii::t('app', 'Inversion'),
            'opacity' => Yii::t('app', 'Opacity'),
            'borderRadius' => Yii::t('app', 'Border radius'),
            'inputRadius' => Yii::t('app', 'Input radius'),
            'customCss' => Yii::t('app', 'Custom CSS'),
            'inputColor' => Yii::t('app', 'Input text color'),
            'inputBgColor' => Yii::t('app', 'Input background color'),
            'buttonColor' => Yii::t('app', 'Button text color'),
            'buttonBgColor' => Yii::t('app', 'Button background color'),
            'cbColor' => Yii::t('app', 'Form text color'),
            'cbBgColor' => Yii::t('app', 'Form background color'),
            'timerColor' => Yii::t('app', 'Timer text color'),
            'timerBgColor' => Yii::t('app', 'Timer background color'),
            'darkenColor' => Yii::t('app', 'Darken color'),
            'shadowSize' => Yii::t('app', 'Shadow size'),
            'shadowBlur' => Yii::t('app', 'Shadow blur'),
            'shadowColor' => Yii::t('app', 'Shadow color'),
        ]);

    }

}
