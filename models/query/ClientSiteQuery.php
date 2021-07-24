<?php

namespace app\models\query;
use app\models\Variable;
use app\models\VariableValue;
use app\models\User;

/**
 * This is the ActiveQuery class for [[\app\models\ClientSite]].
 *
 * @see \app\models\ClientSite
 */
class ClientSiteQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \app\models\ClientSite[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\ClientSite|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Возвращает запрос сайтов созданных в заданный период.
     *
     * @param integer $start Начало периода
     * @param integer|null $end Конец периода, если не задано - используется текущее время.
     * @return $this
     */
    public function createdBetween($start, $end = null)
    {
        if (!isset($end)) {
            $end = time();
        }
        return $this->andWhere(['>', 'created_at', $start])
            ->andWhere(['<', 'created_at', $end]);
    }

    /**
     * Возвращает запрос сайтов возраст которых находится в пределах заданного периода.
     *
     * @param integer $min Минимальный возраст сайта
     * @param integer $max Максимальный возраст сайта
     * @param integer $step Шаг. Например для того чтобы интерпретировать $min и $max параметры как дни,
     * надо задать $step = 86400 (60 * 60 * 24)
     * @return ClientSiteQuery
     */
    public function ageBetween($min, $max, $step = 1)
    {
        $time = time();
        $min = isset($min) ? $min : 0;
        $max = isset($max) ? $max : $time;
        return $this->createdBetween($time - $max * $step, $time - $min * $step);
    }

    /**
     * Возвращает запрос сайтов для тех юзеров у которых нет активного тарифа и по заданному возрасту.
     *
     * @param integer $min Минимальный возраст сайта
     * @param integer $max Максимальный возраст сайта
     * @param integer $step Шаг. Например для того чтобы интерпретировать $min и $max параметры как дни,
     * @return ClientSiteQuery
     */
    public function newInactive($min, $max = null, $step = 86400)
    {
        if (!isset($max)) {
            $max = 2 * $min;
        }
        $this->andWhere(['user_id' => User::find()->select('id')->withNoActiveTariff()]);
        $this->ageBetween($min, $max, $step);
        $sql = $this->createCommand()->rawSql;
        echo $sql . PHP_EOL;
        return $this;
    }

}
