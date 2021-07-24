<?php

namespace app\widgets\charts;

use app\models\ConversionSearch;
use app\themes\inspinia\widgets\IBoxWidget;
use flyiing\helpers\Html;
use fruppel\googlecharts\GoogleCharts;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class ConversionChart extends Widget {

    const TYPE_CONVERSION = 'conversion';
    const TYPE_QUERIES = 'queries';
    const TYPE_VISITS_QUERIES = 'visits_queries';
    const TYPE_VISITS = 'visits';

    public $ibox = true;
    public $title;
    public $summary = true;

    /** @var \app\models\ConversionSearch */
    public $conversionSearch;

    public $type;

    public $chartOptions = [];

    public $summaryOptions = [];

    protected $_config;

    protected function getModels()
    {
        $search = $this->conversionSearch->search([]);
        /** @var \yii\db\Query $query */
        $query = $search->query;
        return $query->all();
    }

    protected function getCols()
    {
        return [];
    }

    protected function getRows()
    {
        return [];
    }

    protected function renderSummary($data)
    {
        $content = '';
        $count = count($data);
        if ($count < 1) {
            return $content;
        }
        $csz = round(12 / $count);
        foreach ($data as $k => $v) {
            $label = Html::tag('small', $k, ['class' => 'stats-label']);
            $value = Html::tag('h4', $v);
            $content .= Html::tag('div', $label . $value, ['class' => 'col-xs-' . $csz]);
        }
        if (strlen($content) > 0) {
            $content = Html::tag('div', $content, $this->summaryOptions);
        }
        return $content;
    }

    /**
     * @param bool $previous
     * @return \app\models\Conversion
     */
    protected function getSummaryValue($previous = false)
    {
        $models = $this->conversionSearch->getModels(['ConversionSearch' => [
            'groupBy' => ConversionSearch::GROUP_BY_ALL,
        ]], $previous);
        return $models[0];
    }

    protected function getSummary()
    {
        return '';
    }

    protected function initConfig()
    {
    }

    public function init()
    {
        if (!(isset($this->conversionSearch) && $this->conversionSearch instanceof ConversionSearch)) {
            throw new InvalidConfigException('$conversionSearch is required.');
        }
        if (!isset($this->type)) {
            $this->type = static::TYPE_VISITS;
        }
        parent::init();
        Html::addCssClass($this->summaryOptions, 'row');
        $this->initConfig();
        $this->_config['data'] = [
            'cols' => $this->getCols(),
            'rows' => $this->getRows(),
        ];
    }

    public function run()
    {
        $content = GoogleCharts::widget(ArrayHelper::merge($this->_config, $this->chartOptions));
        if (isset($this->summary)) {
            if (is_string($this->summary)) {
                $content .= $this->summary;
            } else if ($this->summary === true) {
                $content .= $this->getSummary();
            }
        }
        if ($this->ibox) {
            return IBoxWidget::widget([
                'content' => $content,
                'title' => $this->title,
            ]);
        } else {
            return (isset($this->title) && $this->title ? Html::tag('h5', $this->title) : '') . $content;
        }
    }

}
