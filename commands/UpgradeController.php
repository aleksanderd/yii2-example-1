<?php

namespace app\commands;

use app\models\Variable;
use app\models\VariableValue;
use yii\console\Controller;
use yii\helpers\Console;

class UpgradeController extends Controller {

    protected function convertW2VariableCopy(VariableValue $value, $name)
    {
        Variable::sSet($name, $value->value, $value->user_id, $value->site_id, $value->page_id);
    }

    protected function convertW2Variable($name, $callback)
    {
        $vId = Variable::name2Id($name);
        /** @var \app\models\VariableValue $values */
        $values = VariableValue::find()
            ->where(['variable_id' => $vId])
            ->orderBy(['user_id' => SORT_ASC, 'site_id' => SORT_ASC, 'page_id' => SORT_ASC])
            ->all();
        $this->stdout($name .': '. count($values) . PHP_EOL);
        foreach ($values as $v) {
            printf('%04d:%04d:%04d' . PHP_EOL, $v->user_id, $v->site_id, $v->page_id);
            if (is_string($callback)) {
                $this->convertW2VariableCopy($v, $callback);
            } else if (is_callable($callback)) {
                $callback($v);
            } else {
                $this->stdout('wrong callback!' . PHP_EOL, Console::FG_RED);
            }
        }
    }

    public function actionWidget2() {

        $this->convertW2Variable('w.settings.language', 'w.options.language');
        $this->convertW2Variable('w.settings.restoreInfo', 'w.options.restoreInfo');
        $this->convertW2Variable('w.settings.defaultPrefix', 'w.options.defaultPrefix');
        $this->convertW2Variable('w.settings.startDelay', 'w.options.startDelay');

        $this->convertW2Variable('w.settings.style', function(VariableValue $v) {
            if ($v->value == 'cbw-material') {
                Variable::sSet('w.options.modalOptions.borderRadius', 0, $v->user_id, $v->site_id, $v->page_id);
            }
        });

        $this->convertW2Variable('w.settings.styleColor', function(VariableValue $v) {
            if ($v->value == 'cbw-dark') {
                Variable::sSet('w.options.modalOptions.bgColor', '#272727', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.modalOptions.color', '#fff', $v->user_id, $v->site_id, $v->page_id);
            } else if ($v->value == 'cbw-image') {
                Variable::sSet('w.options.modalOptions.bgImage', 'blue-smooth.jpg', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.modalOptions.bgColor', '#000', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.modalOptions.color', '#fff', $v->user_id, $v->site_id, $v->page_id);
            }
        });

        $this->convertW2Variable('w.settings.styleDirection', function(VariableValue $v) {
            if ($v->value == 'cbw-right') {
                Variable::sSet('w.options.modalOptions.position', 'right', $v->user_id, $v->site_id, $v->page_id);
            }
        });

        $this->convertW2Variable('w.settings.btnStyle', function(VariableValue $v) {
            if ($v->value == 'cbb-green') {
                Variable::sSet('w.options.buttonOptions.baseColor', 'rgba(89, 212, 110, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.activeColor', 'rgba(89, 212, 110, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.image', 'puffy-lw.png', $v->user_id, $v->site_id, $v->page_id);
            } else if ($v->value == 'cbb-blue') {
                Variable::sSet('w.options.buttonOptions.baseColor', 'rgba(85, 189, 255, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.activeColor', 'rgba(85, 189, 255, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.image', 'diskapp-w.png', $v->user_id, $v->site_id, $v->page_id);
            } else if ($v->value == 'cbb-black') {
                Variable::sSet('w.options.buttonOptions.baseColor', 'rgba(37, 37, 37, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.activeColor', 'rgba(37, 37, 37, 0.7)', $v->user_id, $v->site_id, $v->page_id);
                Variable::sSet('w.options.buttonOptions.image', 'mobile-w.png', $v->user_id, $v->site_id, $v->page_id);
            }
        });

        $this->convertW2Variable('w.settings.intervalMin', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.minInterval', $v->value, $v->user_id, $v->site_id, $v->page_id);
            Variable::sSet('w.options.triggersOptions.startInterval', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });

        $this->convertW2Variable('w.settings.forcedModalDelay', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.period.delay', $v->value, $v->user_id, $v->site_id, $v->page_id);
            if ($v->value > 0) {
                Variable::sSet('w.options.triggersOptions.period.action', 'showModal', $v->user_id, $v->site_id, $v->page_id);
            } else {
                Variable::sSet('w.options.triggersOptions.period.action', 'ignore', $v->user_id, $v->site_id, $v->page_id);
            }
        });

        $this->convertW2Variable('w.settings.pageEndAction', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.scrollEnd.action', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });
        $this->convertW2Variable('w.settings.pageEndPercent', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.scrollEnd.pct', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });

        $this->convertW2Variable('w.settings.selectionAction', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.selectText.action', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });
        $this->convertW2Variable('w.settings.selectionMin', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.selectText.minCount', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });
        $this->convertW2Variable('w.settings.selectionDelay', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.selectText.actionDelay', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });

        $this->convertW2Variable('w.settings.mouseLeaveAction', function(VariableValue $v) {
            Variable::sSet('w.options.triggersOptions.mouseExit.action', $v->value, $v->user_id, $v->site_id, $v->page_id);
        });

    }

}
