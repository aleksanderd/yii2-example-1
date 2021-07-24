<?php

namespace app\widgets;

use app\helpers\DataHelper;
use Yii;
use DateTimeZone;
use kartik\select2\Select2;

/**
 * Виджет селектора для выбора временной зоны.
 */
class SelectTimezone extends Select2
{

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            $data = [];
            foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $tzName) {
                $data[$tzName] = DataHelper::timezoneFull($tzName);
            }
            uasort($data, function ($a, $b) {
                return ($a[0] != $b[0] || $a[0] == '-') ? $a < $b : $a > $b;
            });
            $config['data'] = $data;
        }
        parent::__construct($config);
    }

}
