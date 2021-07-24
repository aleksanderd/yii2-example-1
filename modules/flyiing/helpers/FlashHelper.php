<?php

namespace flyiing\helpers;

use Yii;

class FlashHelper
{

    /**
     * Put model errors array[returned by $model->getErrors()] into session flash messages
     * @param $errors
     * @param bool $showUnknownError
     */
    public static function flashModelErrors($errors, $showUnknownError = true)
    {
        $flash = false;
        if (sizeof($errors) > 0) {
            $flash = [];
            foreach($errors as $attr => $messages)
                foreach($messages as $msg)
                    $flash[] .= sprintf('[%s] %s !!!', $attr, $msg);
        } else if($showUnknownError) {
            $flash = Yii::t('user', 'Unknown error');
        }
        if($flash !== false) {
            Yii::$app->session->setFlash('error', $flash);
        }
    }

    public static function setFlash($key, $value = true, $removeAfterAccess = true)
    {
        if(isset(Yii::$app->session)) {
            Yii::$app->session->setFlash($key, $value, $removeAfterAccess);
        }
    }

    public static function addFlash($key, $value = true, $removeAfterAccess = true)
    {
        if(isset(Yii::$app->session)) {
            Yii::$app->session->addFlash($key, $value, $removeAfterAccess);
        }
    }


}