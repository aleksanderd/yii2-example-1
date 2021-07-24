<?php

namespace flyiing\translation;

use Yii;
use flyiing\translation\models\TMessage;
use flyiing\translation\models\TSourceMessage;

class I18N extends \yii\i18n\I18N
{

    public function init()
    {
        if (!isset($this->translations['app']) && !isset($this->translations['app*'])) {
            $this->translations['app'] = [
                'class' => 'yii\i18n\DbMessageSource',
                'sourceLanguage' => Yii::$app->sourceLanguage,
                'sourceMessageTable' => TSourceMessage::tableName(),
                'messageTable' => TMessage::tableName(),
            ];
            $this->translations['*'] = [
                'class' => 'yii\i18n\DbMessageSource',
                'sourceLanguage' => Yii::$app->sourceLanguage,
                'sourceMessageTable' => TSourceMessage::tableName(),
                'messageTable' => TMessage::tableName(),
            ];
        }
        parent::init();
    }

}
