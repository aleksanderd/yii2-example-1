<?php

namespace app\widgets\slider;

class IonSlider extends \yii2mod\slider\IonSlider {

    protected function registerAssets()
    {
        $view = $this->getView();
        IonSliderAsset::register($view);
        $js = '$("#' . $this->options['id'] . '").ionRangeSlider(' . $this->getPluginOptions() . ');';
        $view->registerJs($js, $view::POS_END);
    }

}
