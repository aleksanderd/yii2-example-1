<?php

namespace app\models\variable;

/**
 * Модель текстов для клиентского виджета (En)
 *
 */
class WTextsEn extends WTextsBase
{
    public function __construct($config = [])
    {
        $this->language = 'en';
        if (!isset($config['name'])) {
            $config['name'] = 'w.texts.en';
        }
        parent::__construct($config);
    }

}
