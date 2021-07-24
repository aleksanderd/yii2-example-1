<?php

namespace app\modules\support\controllers;

use app\models\Notification;
use app\models\Variable;
use app\modules\support\models\SupportForm;
use flyiing\helpers\FlashHelper;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class RequestController extends Controller
{

    public function actionForm()
    {
        $model = new SupportForm([
            'url' => Yii::$app->request->referrer,
        ]);
        /** @var \app\models\User $user */
        if ($user = Yii::$app->user->identity) {
            $model->user_id = $user->id;
            $model->name = $user->username;
            $model->email = $user->getNotifyEmail();
        }

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }
            if ($model->validate()) {
                $body = $model->message;
                $mail = new Notification([
                    'type' => Notification::TYPE_EMAIL,
                    'from' => $model->email,
                    'to' => Variable::sGet('s.settings.supportEmail'),
                    'subject' => $model->subject,
                    'body' => $body,
                ]);
                if ($mail->sendMail()) {
                    FlashHelper::setFlash('success', Yii::t('app', 'Support request sent.'));
                    return $this->render('sent', compact('model'));
                } else {
                    FlashHelper::setFlash('error', Yii::t('app', 'Support request send failed.') .'<br>'. $mail->description);
                }
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }
        }
        return $this->render('form', compact('model'));
    }

}
