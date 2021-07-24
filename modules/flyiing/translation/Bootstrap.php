<?php

namespace flyiing\translation;

use flyiing\translation\commands\MessageController;
use flyiing\translation\commands\TranslateController;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap['message'] = MessageController::className();
            if (!isset($app->controllerMap['translate'])) {
                $app->controllerMap['translate'] = TranslateController::className();
            }
        }
    }

}