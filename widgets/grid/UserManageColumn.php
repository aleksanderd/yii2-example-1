<?php

namespace app\widgets\grid;

use app\models\User;
use app\models\UserReferral;
use flyiing\helpers\Html;
use kartik\grid\DataColumn;
use Yii;
use yii\bootstrap\Button;
use yii\helpers\ArrayHelper;

class UserManageColumn extends DataColumn {

    public function __construct($config = [])
    {
        parent::__construct(ArrayHelper::merge([
            'label' => Yii::t('app', 'Manage'),
            'filter' => false,
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'content' => function($m) {
                if (($m instanceof UserReferral) && ($m->p_access != UserReferral::ACCESS_ALLOW)) {
                    return Html::tag('span', Html::icon('ban') . Yii::t('app', 'Denied'), ['class' => 'label label-warning']);
                }
                ;
                if (!($user = $m instanceof User ? $m : ArrayHelper::getValue($m, 'user'))) {
                    return '-';
                }
                /** @var \app\models\User $user */
                $filter = ['user_id' => $user->id];
                $lOpts = [
                    'data-pjax' => 0,
                    'target' => '_blank',
                  //  'class' => 'btn-xs btn-white',
                ];
                $items = [
                    [
                        'label' => Yii::t('app', 'Websites'),
                        'url' => ['//client-site/index', 'ClientSiteSearch' => $filter],
                        'linkOptions' => $lOpts,
                    ],
                    [
                        'label' => Yii::t('app', 'Pages'),
                        'url' => ['//client-page/index', 'ClientPageSearch' => $filter],
                        'linkOptions' => $lOpts,
                    ],
                    [
                        'label' => Yii::t('app', 'Phone lines'),
                        'url' => ['//client-line/index', 'ClientLineSearch' => $filter],
                        'linkOptions' => $lOpts,
                    ],
                    [
                        'label' => Yii::t('app', 'Rules'),
                        'url' => ['//client-rule/index', 'ClientRuleSearch' => $filter],
                        'linkOptions' => $lOpts,
                    ],
                    [
                        'label' => '',
                        'options' => [
                            'role' => 'separator',
                            'class' => 'divider',
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Queries'),
                        'url' => ['//client-query/index', 'ClientQuerySearch' => $filter],
                        'linkOptions' => $lOpts,
                    ],
                ];
                $content = Button::widget([
                    'encodeLabel' => false,
                    'label' => Yii::t('app', 'Manage') . ' <span class="caret"></span>',
                    'options' => [
                        'class' => 'dropdown-toggle btn-sm btn-white',
                        'data-toggle' => 'dropdown',
                    ],
                ]);
                $menu = '';
                foreach ($items as $item) {
                    $body = ArrayHelper::getValue($item, 'label', '');
                    if ($url = ArrayHelper::getValue($item, 'url')) {
                        $body = Html::a($body, $url, ArrayHelper::getValue($item, 'linkOptions', []));
                    }
                    $menu .= Html::tag('li', $body, ArrayHelper::getValue($item, 'options', []));
                }
                $content .= Html::tag('ul', $menu, ['class' => 'dropdown-menu']);
                return Html::tag('div', $content, ['class' => 'btn-group']);
            },
        ], $config));
    }

}
