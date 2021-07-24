<?php

namespace app\controllers\user;

class SecurityController extends \dektrium\user\controllers\SecurityController {

    public $enableCsrfValidation = false;
    
    public function actionLogin()
    {
        $this->layout = '/login';
        return parent::actionLogin();
    }

}