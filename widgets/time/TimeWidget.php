<?php

namespace app\widgets\time;

use Yii;
use flyiing\widgets\base\JQueryInputWidget as BaseWidget;
use flyiing\helpers\Html;

/**
 * Widget for select hours
 */
class TimeWidget extends BaseWidget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    /**
     * Register widget assets
     * @return null
     */
    public function registerAssets()
    {
        TimeWidgetAsset::register($this->view);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $selected = $this->model->{$this->attribute} ?: [];
        if (empty($this->options['class'])) {
            $this->options['class'] = '';
        }
        $this->options['class'] .= ' time-widget';
        $content = Html::beginTag('div', $this->options);
        $content .= '<a class = "all btn btn-xs">' . Yii::t('app', 'Select All') . '</a>';
        $content .= ' | <a class = "business btn btn-xs" data-toggle = "business">' . Yii::t('app', 'Business Hours') . '</a>';
        $content .= ' | <a class = "non-business btn btn-xs" data-toggle = "non-business">' . Yii::t('app', 'Non-business Hours') . '</a>';
        $content .= '<table><tr><td class="variable-checkbox">';
        foreach ([
            1 => Yii::t('app', 'Mon'),
            2 => Yii::t('app', 'Tue'),
            3 => Yii::t('app', 'Wed'),
            4 => Yii::t('app', 'Thu'),
            5 => Yii::t('app', 'Fri'),
            6 => Yii::t('app', 'Sat'),
            0 => Yii::t('app', 'Sun'),
        ] as $day => $title) {
            $content .= '<tr class = "weekdays"><td><a class = "btn btn-xs" data-toggle = "weekday-' . $day . '">' . $title . '</a></td>';
            foreach (range($day*24, $day*24+23) as $hour) {
                $classes = [
                    'weekday-' . $day,
                    'hour-' . ($hour%24),
                ];
                if ($day < 5 && ($hour%24) > 7 && ($hour%24) < 21) {
                    $classes[] = 'business';
                }
                if ($day >= 5 && ($hour%24) > 7 && ($hour%24) < 21) {
                    $classes[] = 'non-business';
                }
                $content .= '<td>' . Html::checkbox($this->name . '[]', in_array($hour, $selected), [
                   'value'   => $hour,
                   'label'   => null,
                   'class'   => implode(' ', $classes),
                ]) . '</td>';
            }
            $content .= '</tr>';
        }
        $content .= '<tr class = "hours"><td">&nbsp;</td><td></td>';
        foreach (range(0, 23) as $k) {
            $content .= '<td><a class = "btn btn-xs" data-toggle = "hour-' . $k . '">' . sprintf('%02d', $k) . '</a></td>';
        }
        $content .= '</tr></table><div class = "clearfix"></div>';
        $content .= Html::endTag('div');
        return $content;
    }
}
