<?php

namespace app\modules\payments\controllers;

use app\modules\payments\components\Paymaster;
use flyiing\helpers\FlashHelper;
use Yii;
use app\models\Payment;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class PaymasterController extends Controller
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
            'method' => Payment::METHOD_PAYMASTER,
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
        return $this->render('/add', compact('model'));
    }

    public function paymentInit(Payment $payment)
    {
        $sParams = ArrayHelper::getValue(Yii::$app->params, 'paymaster', []);
        ArrayHelper::remove($sParams, 'key');
        $desc = Yii::t('app', 'Add funds to G.M.C.F. service for user {user}. Payment ID = {id}.', [
            'user' => $payment->user->username,
            'id' => $payment->id,
        ]);
        $params = ArrayHelper::merge($sParams, [
            'LMI_PAYMENT_AMOUNT' => $payment->amount,
            'LMI_CURRENCY' => Yii::$app->currencyCode,
            'LMI_PAYMENT_NO' => $payment->id,
            'LMI_PAYMENT_DESC_BASE64' => base64_encode($desc),
            //'LMI_PAYER_PHONE_NUMBER' => '',
            //'LMI_PAYER_EMAIL' => '',

            'LMI_INVOICE_CONFIRMATION_URL' => Url::to(['invoice'], true),
            'LMI_PAYMENT_NOTIFICATION_URL' => Url::to(['payment'], true),
            'LMI_SUCCESS_URL' => Url::to(['/payment/complete', 'pid' => $payment->id, 'success' => 1], true),
            'LMI_FAILURE_URL' => Url::to(['/payment/complete', 'pid' => $payment->id, 'success' => 0], true),
        ]);
        return $this->redirect('https://paymaster.ru/Payment/Init?'. http_build_query($params));
    }

    public function actionInvoice()
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/paymaster-invoice.log'), 'a');
        fwrite($f, print_r($post, true));

        $pid = ArrayHelper::getValue($post, 'LMI_PAYMENT_NO', 0);
        if ($pid > 0 && ($payment = Payment::findOne($pid))) {
            $result = 'YES';
        } else {
            $result = 'NO PAYMENT';
        }

        fwrite($f, $result . PHP_EOL);
        fclose($f);
        return $result;
    }

    private function paymasterMd5($params, $returnHash = false)
    {
        if (!is_array($params)) {
            $params = Yii::$app->request->post();
        }
        $md5params = 'LMI_MERCHANT_ID;LMI_PAYMENT_NO;LMI_SYS_PAYMENT_ID;LMI_SYS_PAYMENT_DATE;LMI_PAYMENT_AMOUNT;LMI_CURRENCY;LMI_PAID_AMOUNT;LMI_PAID_CURRENCY;LMI_PAYMENT_SYSTEM;LMI_SIM_MODE';
        $values = [];
        foreach (explode(';', $md5params) as $p) {
            $values[] = ArrayHelper::getValue($params, $p, '');
        }
        $values[] = ArrayHelper::getValue(Yii::$app->params, 'paymaster.key', '');
        $hash = base64_encode(md5(implode(';', $values), true));
        return $returnHash ? $hash : isset($params['LMI_HASH']) && $params['LMI_HASH'] == $hash;
    }

    public function actionPayment()
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/paymaster-payment.log'), 'a');
        fwrite($f, print_r($post, true));

        if ($this->paymasterMd5($post)) {
            if (($pid = ArrayHelper::getValue($post, 'LMI_PAYMENT_NO', false)) !== false) {
                if (!($payment = Payment::findOne($pid))) {
                    fwrite($f, 'Payment not found.' . PHP_EOL);
                    $payment = new Payment();
                }
                $payment->status = Payment::STATUS_COMPLETED;
                if ($payment->save()) {
                    fwrite($f, 'Payment complete.' . PHP_EOL);
                    $payment->createTransaction()->save();
                } else {
                    fwrite($f, 'Payment failed.' . PHP_EOL);
                    fwrite($f, print_r($payment->getErrors(), true));
                }
            } else {
                fwrite($f, 'LMI_PAYMENT_NO is not set!' . PHP_EOL);
            }
        } else {
            fwrite($f, 'Wrong hash: ' . $this->paymasterMd5($post, true) . PHP_EOL);
        }
        fclose($f);
    }

    public function actionDirectAuth()
    {
        $p = new Paymaster();
        $data = [
            'response_type' => 'code',
            'client_id' => ArrayHelper::getValue(Yii::$app->params, 'paymaster.LMI_MERCHANT_ID', ''),
            'redirect_uri' => Url::to(['direct-token'], true),
            'scope' => 3,
        ];
        $p->directPost('https://paymaster.ru/direct/security/auth', $data);
        //return $this->render('direct-auth', compact('data'));
    }

    public function actionDirectToken()
    {
        return 'token';
    }

}
