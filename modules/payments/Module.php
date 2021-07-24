<?php

namespace app\modules\payments;

use app\models\Payment;
use Yii;
use yii\helpers\Url;

class Module extends \yii\base\Module
{

    public $controllerNamespace = 'app\modules\payments\controllers';

    public function init()
    {

    }

    public function getMethods()
    {
        return [
            Payment::METHOD_YAKASSA => [
                'name' => Yii::t('app', 'Yandex.Kassa'),
                'logo' => 'yandex.kassa.png',
                'url' => Url::to(['yandex/add']),
            ],
            Payment::METHOD_PAYPAL => [
                'name' => Yii::t('app', 'Paypal'),
                'logo' => 'paypal.png',
                'url' => Url::to(['paypal/add']),
            ],
            Payment::METHOD_PAYMASTER => [
                'name' => Yii::t('app', 'Paymaster'),
                'logo' => 'paymaster.png',
                'url' => Url::to(['paymaster/add']),
            ],
        ];
    }

}