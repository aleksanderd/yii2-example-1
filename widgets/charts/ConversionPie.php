<?php

namespace app\widgets\charts;

use app\helpers\ViewHelper;
use app\models\ConversionSearch;
use flyiing\helpers\Html;
use Yii;
use yii\base\InvalidConfigException;

class ConversionPie extends ConversionChart {

    protected function getCols()
    {
        return [
            [
                'label' => Yii::t('app', 'Indicator'),
                'type' => 'string',
            ],
            [
                'label' => Yii::t('app', 'Value'),
                'type' => 'number',
            ],
        ];
    }

    protected function getRows()
    {
        $models = $this->getModels();
        if (count($models) != 1) {
            throw new InvalidConfigException('required exactly one Conversion model');
        }
        /** @var \app\models\Conversion $val */
        $val = $models[0];
        if ($this->type == static::TYPE_CONVERSION) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Conversion pie');
            }
            $this->_config['options']['colors'] = ['blue', 'green'];
            $rows = [
                [
                    ['v' => Yii::t('app', 'Conversion')],
                    ['v' => floatval(sprintf('%.2f', $val->visits_unique > 0 ? 1001 * $val->queries / $val->visits_unique : 0))],
                ],
                [
                    ['v' => Yii::t('app', 'Success conversion')],
                    ['v' => floatval(sprintf('%.2f', $val->visits_unique > 0 ? 1001 * $val->queries_success / $val->visits_unique : 0))],
                ],
            ];
        } else if ($this->type == static::TYPE_QUERIES) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Queries pie');
            }
            $this->_config['options']['colors'] = ['#69B8CA', '#9CD9CD'];
            $rows = [
                [
                    ['v' => Yii::t('app', 'Failed queries')],
                    ['v' => $val->queries_failed],
                ],
                [
                    ['v' => Yii::t('app', 'Success queries')],
                    ['v' => $val->queries_success],
                ],
            ];
        } else if ($this->type == static::TYPE_VISITS_QUERIES) {
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Visits and queries pie');
            }
            $this->_config['options']['colors'] = ['gray', 'cyan'];
            $rows = [
                [
                    ['v' => Yii::t('app', 'Unique visits')],
                    ['v' => $val->visits_unique],
                ],
                [
                    ['v' => Yii::t('app', 'Queries')],
                    ['v' => $val->queries],
                ],
            ];
        } else { // VISITS
            if (!isset($this->title)) {
                $this->title = Yii::t('app', 'Visits pie');
            }
            $this->_config['options']['colors'] = ['blue', 'gray', 'green'];
            $rows = [
                [
                    ['v' => Yii::t('app', 'Unique visits')],
                    ['v' => $val->visits_unique],
                ],
                [
                    ['v' => Yii::t('app', 'Return visits')],
                    ['v' => $val->visits - $val->visits_unique],
                ],
                [
                    ['v' => Yii::t('app', 'Queries')],
                    ['v' => $val->queries],
                ],
            ];
        }
        $result = [];
        foreach ($rows as $v) {
            $result[] = ['c' => $v];
        }
        return $result;
    }

    protected function getSummary()
    {
        /** @var \app\models\Conversion $val */
        $val = $this->getSummaryValue();
        $pval = $this->getSummaryValue(true);
        $content = '';
        if ($this->type == static::TYPE_QUERIES) {
            $content .= Html::tag('h4', Yii::t('app', 'Success queries'));
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%d', $val->queries_success),
                Yii::t('app', 'Last period') => sprintf('%d', $pval->queries_success),
                Yii::t('app', 'Change') => ViewHelper::changeText($val->queries_success, $pval->queries_success, ['format' => '%d%%']),
            ]);
            $content .= Html::tag('h4', Yii::t('app', 'Failed queries'));
            $content .= $this->renderSummary([
                Yii::t('app', 'Current period') => sprintf('%d', $val->queries_failed),
                Yii::t('app', 'Last period') => sprintf('%d', $pval->queries_failed),
                Yii::t('app', 'Change') => ViewHelper::changeText($val->queries_failed, $pval->queries_failed, ['format' => '%d%%']),
            ]);
        }
        if (strlen($content) > 0) {
            $content = Html::tag('div', $content, ['class' => 'row']);
        }
        return $content;
    }

    protected function initConfig()
    {
        $this->_config = [
            'id' => $this->id . '_conversion_chart_' . $this->type .'_'. $this->conversionSearch->hash,
            'responsive' => true,
            'visualization' => 'PieChart',
            'options' => [
                'backgroundColor' => 'transparent',
                'chartArea' => [
                    'chartArea' => [
                        'width' => '90%',
                        'height' => '80%',
                    ],
                ],
                'legend' => [
                    'position' => 'in',
                ],
                'fontSize' => '10',
            ],
        ];
    }

    public function init()
    {
        $this->conversionSearch->groupBy = ConversionSearch::GROUP_BY_ALL;
        parent::init();
    }

}