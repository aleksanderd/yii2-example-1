<?php

namespace app\widgets\rl;

use app\helpers\ModelsHelper;
use app\models\ClientLine;
use Yii;
use flyiing\helpers\Html;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use kartik\sortable\Sortable;
use flyiing\widgets\base\JQueryInputWidget as BaseWidget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Виджет для составления упорядоченного списка линий для связи заданного пользователя.
 *
 * Виджет состоит из селектора, в котором доступны все линии заданного пользователя,
 * кнопки добавления линии в список, и самого списка, который можно сортировать перетаскиванием мышкой.
 *
 * В качестве параметра передается или `user_id` или модель, содержащая свойство `user_id`.
 *
 */
class RuleLineWidget extends BaseWidget
{

    /**
     * @var int Идентификатор пользователя
     */
    public $user_id;
    /**
     * @var string ID элемента выбора пользователя
     */
    public $user_input_id;

    /**
     * @var string URL для получения списка линий
     */
    public $url;

    public function init()
    {
        parent::init();
        if ($this->hasModel()) {
            $this->user_id = $this->model->user_id;
            $this->user_input_id = Html::getInputId($this->model, 'user_id');
        }
        if (!isset($this->user_id)) {
            throw new InvalidConfigException('user_id or model width user_id must be set');
        }
        Html::addCssClass($this->options, 'rule-line-widget');
        $this->registerAssets();
    }

    public function registerAssets()
    {
        RuleLineWidgetAsset::register($this->view);
    }

    public function renderSelector()
    {
        $result = '';
        $baseId = $this->options['id'];
        $id = $baseId . '-select';
        $s2Options = [
            'addon' => [
                'append' => [
                    'content' => Html::button(Html::icon('plus') . Yii::t('app', 'Add'), [
                        'id' => $baseId . '-add-btn',
                        'class' => 'btn btn-success',
                    ]),
                    'asButton' => true,
                ],
            ],
            //'allowClear' => true,
            'hideSearch' => true,
        ];
        $config = [
            'id' => $id,
            'name' => $id,
        ];
        if (isset($this->user_input_id) && isset($this->url) && false) {
            $config['select2Options'] = $s2Options;
            $result .= DepDrop::widget(array_merge($config, [
                'type' => DepDrop::TYPE_SELECT2,
                'pluginOptions' => [
                    'url' => Url::to($this->url),
                    'depends' => [$this->user_input_id],
                ],
            ]));
        } else {
            $result .= Select2::widget(array_merge($config, $s2Options));
        }
        return $result;
    }

    public function run()
    {

        $result = Html::beginTag('div', $this->options);

        $result .= $this->renderSelector();
        $eHint = Html::tag('strong', Yii::t('app', 'Attention! The lines list is empty!'));
        $eHint .= '<br/>' . Yii::t('app', 'You need to add at least one line here before saving the rule. Select a line in selector above and press the Add button.');
        $result .= Html::tag('div', $eHint, [
            'id' => $this->options['id'] . '-empty',
            'class' => 'rule-line-widget-empty',
        ]);
        $result .= Sortable::widget([
            'id' => $this->options['id'] . '-sortable',
            'class' => 'rule-line-widget-sortable',
            'items' => [],
        ]);

        $items = ModelsHelper::getSelectData(ClientLine::findAll(['user_id' => $this->user_id]), [
            'id', 'name' => 'title', 'info'
        ]);
        $this->pluginOptions = array_merge($this->pluginOptions, [
            'name' => $this->name,
            'items' => $items,
            'value' => $this->value,
        ]);
        if (isset($this->user_input_id)) {
            $this->pluginOptions['user_input_id'] = $this->user_input_id;
        }
        $this->registerPlugin('RuleLineWidget');

        $result .= Html::endTag('div');
        return $result;

    }

}
