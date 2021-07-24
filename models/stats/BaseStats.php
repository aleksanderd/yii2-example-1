<?php

namespace app\models\stats;

use app\models\ClientQuery;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class BaseStats extends Model
{

    public $period;

    /** @var array Условия отбора статсов */
    public $condition;

    public static function period2condition($period)
    {
        if (preg_match('/(prev|last)(\d+)(hour|day|week|month|year)/', $period, $m)) {
            $period = intval($m[2]);
            $metric = strtoupper($m[3]);
            if ($m[1] == 'last') {
                $startValue = 'NOW() - INTERVAL ' . $period . ' ' . $metric;
                $endValue = 'NOW()';
            } else {
                $startValue = 'NOW() - INTERVAL ' . 2 * $period . ' ' . $metric;
                $endValue = 'NOW() - INTERVAL ' . $period . ' ' . $metric;
            }
            return '(at >= UNIX_TIMESTAMP(' . $startValue . ') AND at <= UNIX_TIMESTAMP(' . $endValue . '))';
        } else {
            return $period;
        }

    }

    public function getQuery()
    {
        return null;
    }

    public function init()
    {
        parent::init();
        if (!isset($this->condition)) {
            $this->condition = [];
        }
        if (isset($this->period)) {
            $this->period = static::period2condition($this->period);
        }
    }

}
