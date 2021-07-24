<?php

namespace app\models\variable;

/**
 * Модель текстов для клиентского виджета (Ru)
 *
 */
class WTextsRu extends WTextsBase
{
    public function __construct($config = [])
    {
        $this->language = 'ru';
        if (!isset($config['name'])) {
            $config['name'] = 'w.texts.ru';
        }
        parent::__construct($config);
    }

}
