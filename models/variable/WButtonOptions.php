<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

class WButtonOptions extends VariableModel {

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'w.options.buttonOptions';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'animation',
            'position',
            'arealSize',
            'margin',
            'radius',
            'image',
            'baseColor',
            'activeColor',
        ]);

        parent::__construct($config);

        $this->addRule(['arealSize', 'margin'], 'integer', ['min' => 0, 'max' => 150]);
        $this->addRule('radius', 'integer', ['min' => 0, 'max' => 50]);
        $this->addRule(['position', 'image', 'baseColor', 'activeColor', 'animation'], 'string');

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'arealSize' => Yii::t('app', 'Areal size'),
            'position' => Yii::t('app', 'Position'),
            'margin' => Yii::t('app', 'Margin'),
            'radius' => Yii::t('app', 'Circularity'),
            'image' => Yii::t('app', 'Image'),
            'baseColor' => Yii::t('app', 'Base color'),
            'activeColor' => Yii::t('app', 'Active color'),
            'animation' => Yii::t('app', 'Appear animation'),
//            '' => Yii::t('app', ''),
        ]);
    }

}
