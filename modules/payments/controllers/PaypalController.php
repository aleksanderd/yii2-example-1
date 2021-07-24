<?php

namespace app\modules\payments\controllers;

use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction as PaypalTransaction;
use PayPal\Api\Payment as PaypalPayment;
use flyiing\helpers\FlashHelper;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Exception\PayPalConnectionException;
use Yii;
use app\models\Payment;
use app\models\paypal\CreditCard;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PaypalController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
//                    [
//                        'allow' => true,
//                        'actions' => [],
//                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'add', 'payment',
                            //'plans',
                            //'cards', 'add-card', 'delete-card', 'pay-card'
                        ],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-card' => ['post'],
                ],
            ],
        ];
    }

    public function actionAdd()
    {
        $model = new Payment([
            'user_id' => Yii::$app->user->id,
            'status' => Payment::STATUS_NEW,
            'method' => Payment::METHOD_PAYPAL,
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

    /**
     * Запускает процесс платежа через PayPal.
     * @param Payment $payment
     * @return Response
     */
    public function paymentInit(Payment $payment)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $desc = Yii::t('app', 'Add funds to G.M.C.F. service for user {user}. Payment ID = {id}.', [
            'user' => $payment->user->username,
            'id' => $payment->id,
        ]);

        $item = new Item();
        $item->setName($desc)
            ->setCurrency(Yii::$app->currencyCode)
            ->setQuantity(1)
            ->setPrice($payment->amount);
        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setCurrency(Yii::$app->currencyCode)
            ->setTotal($payment->amount);

        $transaction = new PaypalTransaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($desc)
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(Url::to(['payment', 'pid' => $payment->id, 'success' => 1], true))
            ->setCancelUrl(Url::to(['payment', 'pid' => $payment->id, 'success' => 0], true));

        $payment = new PaypalPayment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {

            $payment->create(Yii::$app->paypal->context);

        } catch (PayPalConnectionException $ex) {

            echo '<pre>' . $ex . '</pre>';
            // TODO не годится - нада какое то исключение прокинуть
            exit(1);
        }
        $approvalUrl = $payment->getApprovalLink();
        return $this->redirect($approvalUrl);
    }

    /**
     * Обработка результата платежа через PayPal
     * @param $pid
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionPayment($pid)
    {
        /** @var \app\models\Payment $model */
        if (!($model = Payment::findOne($pid))) {
            throw new NotFoundHttpException('Payment not found');
        }
        $success = ArrayHelper::getValue($_GET, 'success', false);
        $paymentId = ArrayHelper::getValue($_GET, 'paymentId', false);
        $payerId = ArrayHelper::getValue($_GET, 'PayerID', false);

        if ($success && $paymentId && $payerId) {

            $ppContext = Yii::$app->paypal->context;

            $payment = PaypalPayment::get($paymentId, $ppContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);
            try {
                $payment->execute($execution, $ppContext);
                $payment = PaypalPayment::get($paymentId, $ppContext);
                $model->amount = $payment->transactions[0]->amount->total;
                $model->status = Payment::STATUS_COMPLETED;
                $model->details = [
                    'pp_id' => $payment->id,
                    'pp_invoice' => $payment->transactions[0]->invoice_number,
                    'pp_description' => $payment->transactions[0]->description,
                ];
                FlashHelper::setFlash('success', Yii::t('app', 'Thank you! Payment ID: ' . $payment->id));

            } catch (PayPalConnectionException $ex) {
                FlashHelper::setFlash('warning', Yii::t('app', 'PayPal payment error'));
                $model->status = Payment::STATUS_ERROR;
            }


        } else {
            FlashHelper::setFlash('warning', Yii::t('app', 'User Cancelled the PayPal payment'));
            $model->status = Payment::STATUS_CANCELED;
        }

        $model->save();

        if ($model->status == Payment::STATUS_COMPLETED) {
            $this->redirect(['/payment/complete', 'pid' => $model->id]);
        } else {
            $this->redirect(['add']);
        }
    }

    public function actionPlans()
    {
        $pp = Yii::$app->paypal->context;
        $planList = Plan::all(['status' => 'ACTIVE'], $pp);
        print_r($planList);
    }

    public function actionCards()
    {
        /** @var \app\components\PayPal $pp */
        $pp = Yii::$app->paypal;
        $params = [
            'merchant_id' => $pp->merchant_id,
            'sort_by' => 'create_time',
            'sort_order' => 'desc',
        ];
        try {
            $cards = \PayPal\Api\CreditCard::all($params, $pp->context)->getItems();
        } catch (PayPalConnectionException $e) {
            $cards = [];
        }
        $cards = new ArrayDataProvider([
            'allModels' => $cards,
            'sort' => [
                'attributes' => ['create_time']
            ],
        ]);
        return $this->render('cards', compact('cards'));
    }

    public function actionDeleteCard($id)
    {
        /** @var \app\components\PayPal $pp */
        $pp = Yii::$app->paypal;
        try {
            $card = \PayPal\Api\CreditCard::get($id, $pp->context);
            $card->delete($pp->context);
            FlashHelper::setFlash('success', Yii::t('app', 'Credit card deleted successfully'));
        } catch (PayPalConnectionException $e) {
            FlashHelper::setFlash('error', $e->getMessage());
        }
        $this->redirect('cards');
    }

    public function actionAddCard()
    {
        /** @var \app\components\PayPal $pp */
        $pp = Yii::$app->paypal;
        $model = new CreditCard([
            'merchant_id' => $pp->merchant_id,
            'external_customer_id' => $pp->merchant_id . '-' . Yii::$app->user->id,
        ]);
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {
                $ppCard = $model->getApiCreditCard();
                try {
                    $ppCard->create($pp->context);
                    FlashHelper::setFlash('success', Yii::t('app', 'Credit card added successfully.'));
                    $this->redirect('cards');
                } catch (PayPalConnectionException $e) {
                    $data = Json::decode($e->getData());
                    if (!$model->addPaypalErrors($data)) {
                        $name = ArrayHelper::getValue($data, 'name', 'UNKNOWN');
                        FlashHelper::setFlash('error', $e->getMessage() .':'. $name);
                        FlashHelper::setFlash('info', '<pre>' . print_r($data, true) . '</pre>');
                    }
                }
            }
            if ($model->hasErrors()) {
                FlashHelper::flashModelErrors($model->getErrors());
            }
        }
        return $this->render('card-form', compact('model'));
    }

    public function actionPayCard()
    {

        $test = [
            'number' => '4627584544988072',
            'type' => 'visa',
            'expire_month' => '07',
            'expire_year' => '2020',
        ];
        $card = new CreditCard($test);
        if (($e = $card->pay()) !== true) {
            $data = Json::decode($e->getData());
            echo $e->getMessage() . '<br><pre>';
            print_r($data);
            echo '</pre>';
            return 'fail';
        } else {
            return 'success!';
        }
    }

}
