<?php

namespace flyiing\helpers;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class UniHelper {

    public static function getDefaultModelActions(ActiveRecord $model = null)
    {
        $result = [
            'index' => [
                'icon' => 'model-index',
                'label' => Yii::t('app', 'Index'),
                'url' => ['index'],
                'options' => [
                    'data-pjax' => 0,
                    'class' => 'btn-default',
                ],
            ],
            'create' => [
                'icon' => 'model-create',
                'label' => Yii::t('app', 'Add'),
                'url' => ['create'],
                'options' => [
                    'data-pjax' => 0,
                    'class' => 'btn-primary',
                ],
            ],
        ];
        if ($model === null) {
            return $result;
        }
        $pk = $model->getPrimaryKey(true);
        $result = array_merge($result, [
            'view' => [
                'icon' => 'model-view',
                'label' => Yii::t('yii', 'View'),
                'url' => array_merge(['view'], $pk),
                'options' => [
                    'class' => 'btn-info',
                    'data-pjax' => 0,
                ],
            ],
            'update' => [
                'icon' => 'model-update',
                'label' => Yii::t('yii', 'Update'),
                'url' => array_merge(['update'], $pk),
                'options' => [
                    'class' => 'btn-success',
                    'data-pjax' => 0,
                ],
            ],
            'delete' => [
                'icon' => 'model-delete',
                'label' => Yii::t('yii', 'Delete'),
                'url' => array_merge(['delete'], $pk),
                'options' => [
                    'class' => 'btn-xs btn-danger',
                    'data-pjax' => 0,
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ],
            ],
        ]);
        return $result;
    }

    /**
     * Функция возвращает массив элементов, описивающих действия, в основном для рисования кнопок
     * Используется переданный массив для переопределения и дополнения стандартных действий.
     * Каждый элемент может содержать: 'icon', 'url', 'label' и другие опции для html тега.
     *
     * Примеры:
     * ```php
     *   $actions = getModelActions('create');
     *   $actions = getModelActions($model, ['update', 'delete']);
     *   $actions = getModelActions($model, [
     *     'update',
     *     'delete' => [
     *       'icon' => 'custom-icon',
     *       'label' => 'custom label',
     *       'url' => ['custom/route', 'param' => 'value'],
     *     ],
     *   ]);
     * ```
     *
     * @param $model
     * @param null $actions
     * @return array
     */
    public static function getModelActions($model, $actions = null)
    {
        if (is_string($model) || is_array($model)) {
            // вариант с один параметром без модели
            $actions = $model;
            $model = null;
        }
        $defActions = static::getDefaultModelActions($model);
        if ($actions === null) {
            // если действия не заданы, то возвращаем все стандартные
            return $defActions;
        }
        if (is_string($actions)) {
            // если передана строка, то это - одно действие
            $actions = [$actions];
        }
        $result = [];
        foreach ($actions as $key => $act) {
            if (is_string($act)) {
                // если элемент - строка, то...
                if ($defCfg = ArrayHelper::getValue($defActions, $act)) {
                    // если есть стандартный конфиг с таким именем (create, update, view, delete),
                    // то используем его
                    $result[$key] = $defCfg;
                } else {
                    // иначе, используем строку как есть
                    $result[$key] = $act;
                }
            } else if (is_array($act)) {
                // если эелемент - массив, то...
                if (is_string($key) && ($defCfg = ArrayHelper::getValue($defActions, $key))) {
                    // если ключ - строка с названием одного из стандартных конфигов, то
                    // сливаем этот конфиг с переданным
                    if (isset($act['url'])) { // TODO разобраться, а то merge дублирует 0 => 'action'
                        unset($defCfg['url']);
                    }
                    $act = ArrayHelper::merge($defCfg, $act);
                }
                $result[$key] = $act;
            }
        }
        return $result;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function action2button($config)
    {
        if (!isset($config['options'])) {
            $config['options'] = [];
        }
        Html::addCssClass($config['options'], 'btn');
        if ($icon = ArrayHelper::remove($config, 'icon')) {
            $config['encodeLabel'] = false;
            $config['label'] = Html::icon($icon) . ArrayHelper::getValue($config, 'label', '');
        }
        if ($url = ArrayHelper::remove($config, 'url')) {
            $config['tagName'] = 'a';
            $config['options']['href'] = Url::to($url);
        }
        return $config;
    }

    /**
     * @param array $configs
     * @return array
     */
    public static function actions2buttons($configs)
    {
        $result = [];
        foreach ($configs as $config) {
            $result[] = static::action2button($config);
        }
        return $result;
    }

}
