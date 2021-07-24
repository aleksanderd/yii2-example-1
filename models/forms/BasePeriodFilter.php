<?php

namespace app\models\forms;

use Yii;

class BasePeriodFilter extends BaseFilter {

    public $period;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['period'], 'integer'],
        ]);
    }

    public function periodLabels()
    {
        return [
            1 => Yii::t('app', '24 hours'),
            2 => Yii::t('app', '48 hours'),
            7 => Yii::t('app', '7 days'),
            30 => Yii::t('app', '30 days'),
            90 => Yii::t('app', '90 days'),
            365 => Yii::t('app', '12 months'),
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'period' => Yii::t('app', 'Period'),
        ]);
    }

}
