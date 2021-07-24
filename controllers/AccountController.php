<?php

namespace app\controllers;


use app\models\Notification;
use app\models\Variable;
use Yii;
use app\models\User;
use yii\web\Controller;
use app\models\user\RegistrationForm;
use yii\helpers\ArrayHelper;
use dektrium\user\models\LoginForm;

class AccountController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $this->redirect(['/dashboard/index']);
//        return $this->render('index');
    }

    public function actionIsmail()
    {
        $email = ArrayHelper::getValue(Yii::$app, 'request.queryParams.email');
        if ($email && ($user = User::find()->where(compact('email'))->one())) {
            $model = Yii::createObject(LoginForm::className());
            $model->login = $email;
            return $this->renderPartial('form-login', ['model'  => $model]);
        } else {
            $dt = Yii::$app->formatter->asDatetime(time());
            $n = new Notification([
                'type' => Notification::TYPE_EMAIL,
                'from' => Variable::sGet('s.notify.emailFrom'),
                'to' => Variable::sGet('s.notify.emailTo'),
                'subject' => Yii::t('app', 'E-mail entered at gmcf.ru : {email}', ['email' => $email]),
                'body' => Yii::t('app', 'E-mail entered at gmcf.ru : {email} at {dt}', ['email' => $email, 'dt' => $dt]),
            ]);
            $n->send();
            $model = new RegistrationForm([
                'email' => $email,
                'referral' => ArrayHelper::getValue(Yii::$app, 'request.queryParams.r',
                    Yii::$app->request->cookies->getValue('r')),
                'http_referrer' => ArrayHelper::getValue(Yii::$app, 'request.queryParams.http_referrer'),
            ]);
            return $this->renderPartial('form-registration', ['model'  => $model]);
        }
    }

}
