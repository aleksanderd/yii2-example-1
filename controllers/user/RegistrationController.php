<?php

namespace app\controllers\user;

use app\models\user\RegistrationForm;
use flyiing\helpers\FlashHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class RegistrationController extends \dektrium\user\controllers\RegistrationController {

    public $enableCsrfValidation = false;

    public function actionRegister()
    {
        // ??? header('Access-Control-Allow-Origin: *');
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        $this->layout = '/login';

        /** @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());
        if ($r = ArrayHelper::getValue(Yii::$app, 'request.queryParams.r',  Yii::$app->request->cookies->getValue('r'))) {
            $model->referral = $r;
        }
        if ($http_referrer = ArrayHelper::getValue(Yii::$app, 'request.queryParams.http_referrer',
            Yii::$app->request->cookies->getValue('http_referrer'))) {
            $model->http_referrer = $http_referrer;
        }

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            if (!isset($model->http_referrer)) {
                $model->http_referrer = Yii::$app->request->referrer;
            }
            if ($model->register()) {
                FlashHelper::setFlash('success', Yii::t(
                    'user', 'Your account has been created! You can <a href="{url}">login here</a>',
                    ['url' => Url::toRoute('/user/security/login', true)]
                ));
                return $this->render('/message', [
                    'title'  => Yii::t('user', 'Your account has been created'),
                    'module' => $this->module,
                ]);
            }
        }

        return $this->render('register', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }

}
