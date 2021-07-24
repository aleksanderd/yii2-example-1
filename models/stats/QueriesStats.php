<?php

namespace app\models\stats;

use app\models\ClientVisit;
use app\models\WidgetHit;
use Yii;
use app\models\ClientQuery;

/**
 * Class QueriesStats
 * @property \yii\db\ActiveQuery $query
 */
class QueriesStats extends BaseStats
{

    public $visits;
    public $visits_unique;
    public $hits;

    /** @var integer Всего запросов */
    public $total;

    /** @var integer Успешно обработанных запросов */
    public $success;
    public $successPct;

    /** @var integer Количество запросов, которые не получилось обработать */
    public $fail;
    public $failPct;

    /** @var integer По вине менеджера */
    public $failMgr;
    public $failMgrPct;

    public $clientCost;

    public $recordTime;

    public function attributeLabels()
    {
        return [
            'visits' => Yii::t('app', 'Total visits'),
            'visits_unique' => Yii::t('app', 'Unique visits'),
            'hits' => Yii::t('app', 'Total hits'),
            'total' => Yii::t('app', 'Total queries'),
            'success' => Yii::t('app', 'Success queries'),
            'successPct' => Yii::t('app', 'Success ratio'),
            'fail' => Yii::t('app', 'Failed queries'),
            'failPct' => Yii::t('app', 'Fails ratio'),
            'failMgr' => Yii::t('app', 'Failed by manager'),
            'failMgrPct' => Yii::t('app', 'Fails by manager ratio'),
            'clientCost' => Yii::t('app', 'Cost'),
            'recordTime' => Yii::t('app', 'Records time'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return ClientQuery::find()->where($this->period)->andWhere($this->condition);
    }

    public function getVisitsQuery()
    {
        $result = ClientVisit::find()->where($this->period)->andWhere($this->condition);
        $sql = $result->createCommand()->rawSql;
        return $result;
    }

    public function getHitsQuery()
    {
        $result = WidgetHit::find()->where($this->period);
        if (count($this->condition) > 0) {
            $result->andWhere(['IN', 'visit_id', $this->getVisitsQuery()->select('id')]);
        }
        $sql = $result->createCommand()->rawSql;
        return $result;
    }

    public function init()
    {
        parent::init();

        $visits = $this->getVisitsQuery();
        $this->visits = $visits->count();
        $this->visits_unique = $visits->andWhere('previous_id IS NULL')->count();

        $this->hits = $this->getHitsQuery()->count();

        $query = $this->getQuery();
        $this->total = intval($query->count());
        $this->clientCost = floatval($query->sum('client_cost'));
        $this->recordTime = floatval($query->sum('record_time'));
        $sql = $query->createCommand()->rawSql;
        $this->success = intval($query->andWhere('status >= 1000')->count());
        $this->fail = $this->total - $this->success;
        $query = $this->getQuery();
        $this->failMgr = intval($query->andWhere('status > 100 AND status < 200')->count());

        if ($this->total > 0) {
            $this->successPct = 100 * $this->success / $this->total;
            $this->failPct = 100 * $this->fail / $this->total;
            $this->failMgrPct = 100 * $this->failMgr / $this->total;
        } else {
            $this->successPct = $this->failPct = $this->failMgrPct = 0;
        }

    }



}
