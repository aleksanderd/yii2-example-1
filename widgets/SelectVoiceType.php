<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/**
 * Виджет селектора для выбора типа голоса.
 */
class SelectVoiceType extends Select2
{

    public static function languageLabels()
    {
        return [
            'RU_RUSSIAN_FEMALE' => Yii::t('app', 'Female'),
            'RU_RUSSIAN_MALE' => Yii::t('app', 'Male'),
            'US_ENGLISH_FEMALE' => Yii::t('app', 'Female'),
            'US_ENGLISH_MALE' => Yii::t('app', 'Male'),
        ];
    }

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $data = static::languageLabels();
            if ($language = ArrayHelper::remove($config, 'language')) {
                $language = strtoupper($language);
                if ($language == 'EN') {
                    $language = 'US';
                }
            }
            foreach ($data as $id => $text) {
                if (isset($language)) {
                    if (substr($id, 0, 2) != $language) {
                        unset($data[$id]);
                    }
                } else {
                    $data[$id] .= '('. $id .')';
                }
            }
            $config['data'] = $data;
        }
        if (!isset($config['hideSearch'])) {
            $config['hideSearch'] = true;
        }
        parent::__construct($config);
    }

}
