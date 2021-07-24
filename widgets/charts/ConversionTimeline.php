<?php

namespace app\widgets\charts;

use app\helpers\ViewHelper;
use flyiing\helpers\Html;
use Yii;
use app\models\ConversionSearch;

class ConversionTimeline extends ConversionChart {

    protected function getCols()
    {
        $cols = [[
            'label' => Yii::t('app', 'Dates'),
            'type' => 'string',
        ]];
        if ($this->type == static::TYPE_CONVERSION) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Conversion graph');
            }
            $this->_config['visualization'] = 'LineChart';
            $this->_config['options']['colors'] = ['blue', 'green'];
            $this->_config['options']['colors'] = ['#69B8CA', '#9CD9CD'];
            $cols = array_merge($cols, [
                [
                    'label' => Yii::t('app', 'Conversion'),
                    'type' => 'number',
                ],
                [
                    'label' => Yii::t('app', 'Success conversion'),
                    'type' => 'number',
                ],
            ]);
        } else if ($this->type == static::TYPE_QUERIES) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Queries graph');
            }
            $this->_config['visualization'] = 'ColumnChart';
            $this->_config['options']['isStacked'] = false;
            $this->_config['options']['colors'] = ['gray', 'green'];
            $cols = array_merge($cols, [
                [
                    'label' => Yii::t('app', 'Failed queries'),
                    'type' => 'number',
                ],
                [
                    'label' => Yii::t('app', 'Success queries'),
                    'type' => 'number',
                ],
            ]);
        } else if ($this->type == static::TYPE_VISITS_QUERIES) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Visits and queries graph');
            }
            $this->_config['visualization'] = 'ColumnChart';
            $this->_config['options']['isStacked'] = false;
            $this->_config['options']['colors'] = ['#69B8CA', '#9CD9CD'];
            $cols = array_merge($cols, [
                [
                    'label' => Yii::t('app', 'Unique visits'),
                    'type' => 'number',
                ],
                [
                    'label' => Yii::t('app', 'Queries'),
                    'type' => 'number',
                ],
            ]);
        } else { // VISITS
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Visits graph');
            }
            $this->_config['visualization'] = 'AreaChart';
            $this->_config['options']['colors'] = ['#69B8CA', '#9CD9CD'];
            $cols = array_merge($cols, [
                [
                    'label' => Yii::t('app', 'Unique visits'),
                    'type' => 'number',
                ],
                [
                    'label' => Yii::t('app', 'Return visits'),
                    'type' => 'number',
                ],
            ]);
        }
        return $cols;
    }

    protected function getRows()
    {
        $rows = [];
        $fmt = Yii::$app->formatter;
        foreach ($this->getModels() as $val) {
            /** @var \app\models\Conversion $val */
            $returns = $val->visits - $val->visits_unique;
            if ($this->conversionSearch->groupBy === ConversionSearch::GROUP_BY_DT_MONTH) {
                $dt = $fmt->asDate($val->datetime, 'LLLL');
            } else if ($this->conversionSearch->groupBy === ConversionSearch::GROUP_BY_DT_HOUR) {
                $dt = $fmt->asDate($val->datetime, 'HH');
            } else {
                $dt = $fmt->asDate($val->datetime, 'd MMMM');
            }
            $values = [['v' => $dt]];
            if ($this->type == static::TYPE_CONVERSION) {
                $values[] = ['v' => sprintf('%.2f', $val->conversion)];
                $values[] = ['v' => sprintf('%.2f', $val->conversion_success)];
            } else if ($this->type == static::TYPE_QUERIES) {
                $values[] = ['v' => $val->queries_unpaid];
                $values[] = ['v' => $val->queries_failed];
                $values[] = ['v' => $val->queries_success];
            } else if ($this->type == static::TYPE_VISITS_QUERIES) {
                $values[] = ['v' => $val->visits_unique];
                $values[] = ['v' => $val->queries_unique];
            } else { // VISITS
                $values[] = ['v' => $val->visits_unique];
                $values[] = ['v' => $returns > 0 ? $returns : 0];
                //$values[] = ['v' => $val->visits];
            }
            $rows[] = ['c' => $values];
        }
        return $rows;
    }

    protected function getSummary()
    {
        $val = $this->getSummaryValue();
        $pval = $this->getSummaryValue(true);
        $content = '';
        if ($this->type == static::TYPE_VISITS) {
            $content .= $this->renderSummary([
                Yii::t('app', 'Total pages') => $val->hits,
                Yii::t('app', 'Total visits') => $val->visits,
                Yii::t('app', 'Unique visits') => $val->visits_unique,
                Yii::t('app', 'Return visits') => $val->visits - $val->visits_unique,
            ]);
        } else if ($this->type == static::TYPE_CONVERSION) {
            $content .= Html::tag('h4', Yii::t('app', 'Conversion'));
            $curr = $val->conversion;
            $prev = $pval->conversion;
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%.2f%%', $curr),
                Yii::t('app', 'Last period') => sprintf('%.2f%%', $prev),
                Yii::t('app', 'Change') => ViewHelper::changeText($curr, $prev, ['format' => '%d%%']),
            ]);
            $content .= Html::tag('h4', Yii::t('app', 'Success conversion'));
            $curr = $val->conversion_success;
            $prev = $pval->conversion_success;
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%.2f%%', $curr),
                Yii::t('app', 'Last period') => sprintf('%.2f%%', $prev),
                Yii::t('app', 'Change') => ViewHelper::changeText($curr, $prev, ['format' => '%d%%']),
            ]);
        } else if ($this->type == static::TYPE_VISITS_QUERIES) {
            $content .= Html::tag('h4', Yii::t('app', 'Unique visits'));
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%d', $val->visits_unique),
                Yii::t('app', 'Last period') => sprintf('%d', $pval->visits_unique),
                Yii::t('app', 'Change') => ViewHelper::changeText($val->visits_unique, $pval->visits_unique, ['format' => '%d%%']),
            ]);
            $content .= Html::tag('h4', Yii::t('app', 'Unique queries'));
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%d', $val->queries_unique),
                Yii::t('app', 'Last period') => sprintf('%d', $pval->queries_unique),
                Yii::t('app', 'Change') => ViewHelper::changeText($val->queries_unique, $pval->queries_unique, ['format' => '%d%%']),
            ]);
        }
        return $content;
    }

    protected function initConfig()
    {
        $this->_config = [
            'id' => $this->id . '_conversion_chart_' . $this->type .'_'. $this->conversionSearch->hash,
            'responsive' => true,
            'options' => [
                'backgroundColor' => 'transparent',
                'chartArea' => [
                    'left' => 50,
                    'top' => 5,
                    'width' => '100%',
                    'height' => '85%',
                ],
                'isStacked' => true,
                'curveType' => 'function',
                'legend' => [
                    'position' => 'in',
                ],
                'vAxis' => [
                    'viewWindow' => ['min' => 0],
                ],
                'fontSize' => '10',
            ],
        ];
    }

    public function init()
    {
        if (!$this->conversionSearch->isDatetimeGrouped) {
            $this->conversionSearch->groupBy = ConversionSearch::GROUP_BY_DT_DAY;
        }
        parent::init();
    }

}
