<?php

namespace app\widgets\hint;

use app\models\Variable;
use Yii;
use flyiing\helpers\Html;
use yii\base\Widget;
//use yii\jui\Widget;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * HintWidget рисует подсказку, если таковая есть.
 * Так же, отображает ссылку на редактирование переводов сообщения.
 *
 */
class HintWidget extends Widget {

    /** @var string Категория сообщения */
    public $category;

    /** @var string Исходное сообщение */
    public $message;

    public $options;

    public $pluginOptions = [];

    public $hidden;

    public $showButton = true;
    public $showClose = true;

    public function init()
    {
        /** @var \app\models\User $user */
        if ($user = Yii::$app->user->identity) {
            if (!isset($this->pluginOptions['url'])) {
                $this->pluginOptions['url'] = Url::to([
                    '/variable/remote-set',
                    'name' => 'ui.hide.' . $this->message,
                    'user_id' => $user->id,
                ]);
            }
            if (!isset($this->hidden)) {
                $this->hidden = Variable::sGet('ui.hide.' . $this->message, $user->id);
            }
        };
        if (!isset($this->category)) {
            $this->category = 'app.sys';
        }
        Html::addCssClass($this->options, 'hint-widget');
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->id;
        }
    }

    public function run()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $content = '';
        $hint = Yii::t($this->category, $this->message);
        if (strlen($hint) < 1 || $hint == $this->message) {
            if (isset($user) && $user->isAdmin) {
                $hint = Html::tag('small', $hint);
            } else {
                return '';
            }
        }
        if ($this->showClose) {
            $hint = Html::tag('button', '&times;', [
                    'type' => 'button',
                    'tabIndex' => -1,
                    'class' => 'pull-right close hint-toggle',
                ]) . $hint;
        }
        if (isset($user) && $user->isAdmin) {
            $updateUrl = ['/translation/message/edit', 'category' => $this->category, 'message' => $this->message];
            $hint .= ' ' . Html::a(Html::icon('edit'), $updateUrl, ['target' => '_blank', 'tabIndex' => -1]);
        }
        if (!$this->hidden) {
            Html::addCssClass($this->options, 'hint-visible');
        }
        $content .= Html::beginTag('div', $this->options);
        if ($this->showButton) {
            $content .= Html::a(Html::icon('info', '{i}'), null, ['class' => 'hint-toggle']);
        }
        $content .= Html::tag('div', $hint, [
            'class' => 'hint-content',
            'style' => $this->hidden > 0 ? 'display: none' : 'display: inline',
        ]);
        $content .= Html::endTag('div');
        HintWidgetAsset::register($this->view);
        $this->view->registerJs(sprintf('jQuery("#%s").fHint(%s)',
            $this->id, Json::encode($this->pluginOptions)));
        return $content;
    }

}
