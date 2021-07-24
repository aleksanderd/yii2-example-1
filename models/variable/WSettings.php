<?php

namespace app\models\variable;

use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель настроек основных для клиентского виджета
 *
 * @property string $language Язык
 */
class WSettings extends VariableModel
{
    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'w.settings';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'language',

            'style',
            'styleColor',
            'styleDirection',
            'btnStyle',

            'restoreInfo',
            'defaultPrefix',
            'startDelay',

            'forcedModalDelay',
            'intervalMin',
            'pageEndAction',
            'pageEndPercent',
            'selectionAction',
            'selectionDelay',
            'selectionMin',
            'mouseLeaveAction',
        ]);

        parent::__construct($config);

        $this->addRule('language', 'string', ['min' => 2, 'max' => 5]);
        $this->addRule('style', 'string');
        $this->addRule('styleDirection', 'string');
        $this->addRule('btnStyle', 'string');
        $this->addRule('styleColor', 'string');
        $this->addRule('restoreInfo', 'integer', ['min' => 0]);
        $this->addRule('defaultPrefix', 'string', ['min' => 2, 'max' => 5]);
        $this->addRule('startDelay', 'integer', ['min' => 0]);
        $this->addRule('forcedModalDelay', 'integer', ['min' => 0]);
        $this->addRule('intervalMin', 'integer', ['min' => 0]);
        $this->addRule('pageEndPercent', 'integer', ['min' => 0, 'max' => 100]);
        $this->addRule('selectionDelay', 'integer', ['min' => 0]);
        $this->addRule('selectionMin', 'integer', ['min' => 1]);

        $this->addRule([
            'pageEndAction',
            'selectionAction',
            'mouseLeaveAction'
        ], 'string');
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'language' => Yii::t('app', 'Language'),
            'style' => Yii::t('app', 'Style'),
            'styleColor' => Yii::t('app', 'Style color'),
            'styleDirection' => Yii::t('app', 'Widget direction'),
            'btnStyle' => Yii::t('app', 'Button style'),
            'restoreInfo' => Yii::t('app', 'Restore call info'),
            'defaultPrefix' => Yii::t('app', 'Default prefix'),
            'startDelay' => Yii::t('app', 'Start delay'),
            'forcedModalDelay' => Yii::t('app', 'Forced modal delay'),
            'intervalMin' => Yii::t('app', 'Minimal interval'),
            'pageEndAction' => Yii::t('app', 'Page end action'),
            'pageEndPercent' => Yii::t('app', 'Page end percent'),
            'selectionAction' => Yii::t('app', 'Selection action'),
            'selectionDelay' => Yii::t('app', 'Delay after selection'),
            'selectionMin' => Yii::t('app', 'Minimum of selections'),
            'mouseLeaveAction' => Yii::t('app', 'Mouse leave action'),
        ]);
    }

}
