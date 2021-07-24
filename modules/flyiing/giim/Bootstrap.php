<?php

namespace flyiing\giim;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        if($app->hasModule('gii')) {
            $app->getModule('gii')->generators[] = 'flyiing\giim\model\Generator';
            $app->getModule('gii')->generators[] = 'flyiing\giim\crud\Generator';
        }
    }

}