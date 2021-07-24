<?php

namespace app\modules\payments\controllers;

// TODO: Наброски, НЕ РАБОЧЕЕ!!!

use app\components\Paymaster;
use flyiing\helpers\FlashHelper;
use Yii;
use app\models\Payment;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class WalletoneController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['invoice', 'payment'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'direct-auth', 'direct-token'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['invoice', 'payment'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionAdd()
    {
        $model = new Payment([
            'user_id' => Yii::$app->user->id,
            'status' => Payment::STATUS_NEW,
            'method' => Payment::METHOD_WALLETONE,
        ]);
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->paymentInit($model);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('/payment/add', compact('model'));
    }
    public function runWalletone(Payment $payment)
    {
        $params = [
            'WMI_MERCHANT_ID' => ArrayHelper::getValue(Yii::$app->params, 'walletone.WMI_MERCHANT_ID', ''),
            'WMI_PAYMENT_AMOUNT' => $payment->amount,
            'WMI_CURRENCY_ID' => '643',
            'WMI_DESCRIPTION' => 'Add funds to GetMoreCustomersFast account. Username: ' . $payment->user->username,
            'WMI_SUCCESS_URL' => Url::to(['walletone', 'pid' => $payment->id, 'success' => 1]),
            'WMI_FAIL_URL' => Url::to(['walletone', 'pid' => $payment->id, 'success' => 0]),
        ];
        $url = 'https://wl.walletone.com/checkout/checkout/Index';
        return $this->render('walletone', compact('params', 'url'));
//        $this->redirect($url .'?'. http_build_query($params));
    }

    public function actionWalletoneCheck()
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/walletone-check.log'), 'a');
        fwrite($f, print_r($post, true));
        fclose($f);
    }

    public function actionWalletone($pid)
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/walletone.log'), 'a');
        fwrite($f, print_r($post, true));
        $model = $this->findModel($pid);
        $success = ArrayHelper::getValue($_GET, 'success', false);
        if ($success) {
            $model->status = Payment::STATUS_COMPLETED;
            FlashHelper::setFlash('success', Yii::t('app', 'Thank you!'));
        } else {
            $model->status = Payment::STATUS_CANCELED;
            FlashHelper::setFlash('warning', Yii::t('app', 'User canceled walletone payment.'));
        }
        $model->save();
        fclose($f);
        if ($model->status == Payment::STATUS_COMPLETED) {
            $this->redirect(['complete', 'pid' => $model->id]);
        } else {
            $this->redirect(['add']);
        }
    }


}
