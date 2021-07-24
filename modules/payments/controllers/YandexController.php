<?php

namespace app\modules\payments\controllers;

use app\components\Paymaster;
use app\models\PaymentSearch;
use flyiing\helpers\FlashHelper;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction as PaypalTransaction;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Exception\PayPalConnectionException;
use Yii;
use app\models\Payment;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class YandexController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'check-order', 'payment-aviso'
                        ],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'payment'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['check-order', 'payment-aviso'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionAdd()
    {
        $model = new Payment([
            'user_id' => Yii::$app->user->id,
            'status' => Payment::STATUS_NEW,
            'method' => Payment::METHOD_YAKASSA,
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
        $yaKassa = ArrayHelper::getValue(Yii::$app->params, 'yaKassa', []);
        $url = ArrayHelper::getValue($yaKassa, 'url', 'https://demomoney.yandex.ru/eshop.xml');
        $params = ArrayHelper::merge(ArrayHelper::getValue($yaKassa, 'params', []), [
            'cps_email'      => Yii::$app->user->identity->email,
            'cps_phone'      => Yii::$app->user->identity->profile->phone,
            'sum'            => $payment->amount,
            'customerNumber' => $payment->user_id,
            'orderNumber'    => $payment->id,
            'shopSuccessURL' => Url::to(['payment', 'pid' => $payment->id, 'success' => 1], true),
            'shopFailURL' => Url::to(['payment', 'pid' => $payment->id, 'success' => 0], true),
        ]);
        unset($params['shopPassword']);
        return $this->redirect($url .'?'. http_build_query($params));
    }

    public function actionPayment($pid = 0)
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/yandex.log'), 'a');
        fwrite($f, print_r($post, true));
        if (!($model = Payment::findOne($pid))) {
            throw new NotFoundHttpException(Yii::t('app', 'Payment not found'));
        }
        $success = ArrayHelper::getValue($_GET, 'success', false);
        if ($success) {
            $model->status = Payment::STATUS_COMPLETED;
            FlashHelper::setFlash('success', Yii::t('app', 'Thank you!'));
        } else {
            $model->status = Payment::STATUS_CANCELED;
            FlashHelper::setFlash('warning', Yii::t('app', 'User canceled yandex payment.'));
        }
        $model->save();
        fclose($f);
        if ($model->status == Payment::STATUS_COMPLETED) {
            $this->redirect(['/payment/complete', 'pid' => $model->id]);
        } else {
            $this->redirect(['add']);
        }
    }

    private function yandexMd5($params = null)
    {
        if (!is_array($params)) {
            $params = Yii::$app->request->post();
        }
        $md5params = 'action;orderSumAmount;orderSumCurrencyPaycash;orderSumBankPaycash;shopId;invoiceId;customerNumber';
        $md5values = [];
        foreach (explode(';', $md5params) as $p) {
            $md5values[] = ArrayHelper::getValue($params, $p, '');
        }
        $md5values[] = ArrayHelper::getValue(Yii::$app->params, 'yaKassa.params.shopPassword', '');
        $md5 = strtoupper(md5(implode(';', $md5values)));
        return isset($params['md5']) && $params['md5'] == $md5;
    }

    private function yandexResponse($params)
    {
        $action = ArrayHelper::getValue($params, 'action', 'checkOrder');
        $code = ArrayHelper::getValue($params, 'code', 0);
//        $date = ArrayHelper::getValue($params, 'date', date("Y-m-d H:i:s"));
        $date = ArrayHelper::getValue($params, 'requestDatetime', date("Y-m-d H:i:s"));
        $invoiceId = ArrayHelper::getValue($params, 'invoiceId', '');
        $shopId = ArrayHelper::getValue($params, 'shopId', '');
        $result = '<?xml version="1.0" encoding="UTF-8"?>';
        $result .= sprintf('<%sResponse performedDatetime="%s" code="%d" invoiceId="%s" shopId="%s" />',
            $action, $date, $code, $invoiceId, $shopId);
        return $result;
    }

    public function actionCheckOrder()
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/yandex-check-order.log'), 'a');
        fwrite($f, print_r($post, true));
        if (!$this->yandexMd5($post)) {
            $rCode = 1;
        } else if ($payment = Payment::findOne(ArrayHelper::getValue($post, 'orderNumber', 0))) {
            /** @var \app\models\Payment $payment */
            $rCode = 0;
        } else {
            $rCode = 100;
        }

        $post['code'] = $rCode;
        $result = $this->yandexResponse($post);

        fwrite($f, PHP_EOL . $result . PHP_EOL);
        fclose($f);
        return $result;
    }

    public function actionPaymentAviso()
    {
        $post = Yii::$app->request->post();
        $f = fopen(Yii::getAlias('@runtime/logs/yandex-payment-aviso.log'), 'a');
        fwrite($f, print_r($post, true));
        if (!$this->yandexMd5($post)) {
            $rCode = 1;
        } else if ($payment = Payment::findOne(ArrayHelper::getValue($post, 'orderNumber', 0))) {
            /** @var \app\models\Payment $payment */
            $payment->status = Payment::STATUS_COMPLETED;
            $payment->save();
            $payment->createTransaction()->save();
            $rCode = 0;
        } else {
            $rCode = 100;
        }

        $post['code'] = $rCode;
        $result = $this->yandexResponse($post);

        fwrite($f, PHP_EOL . $result . PHP_EOL);
        fclose($f);
        return $result;
    }

}
