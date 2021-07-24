<?php

namespace app\commands;

use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Currency;
use PayPal\Exception\PayPalConnectionException;
use Yii;
use PayPal\Api\Agreement;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class PaymentController extends Controller
{

    public function actionExec()
    {
        $ppContext = Yii::$app->paypal->context;
        $token = 'EC-9NK59896N2463771L';
        $agreement = new Agreement();
        $agreement->execute($token, $ppContext);
        $agreement = Agreement::get($agreement->getId(), $ppContext);
        print_r($agreement);
    }

    public function actionBill()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        echo $date->format(DATE_ISO8601) . PHP_EOL;

        $ppContext = Yii::$app->paypal->context;
        //$agreement = new Agreement();
        $agreement = Agreement::get('I-Y5X1TVYNTDSH', $ppContext);
        print_r($agreement->getAgreementDetails());
        $state = new AgreementStateDescriptor();
        $state->setNote('test note')->setAmount(new Currency([
            'value' => 11,
            'currency' => ArrayHelper::getValue(Yii::$app->params, 'currencyCode', 'RUB')
        ]));

        try {

            $agreement->setBalance(new Currency([
                'value' => 100,
                'currency' => 'RUB',
            ]), $ppContext);
            $agreement->billBalance($state, $ppContext);

        } catch (PayPalConnectionException $e) {
            echo $e->getMessage() . PHP_EOL;
            print_r($e->getData());
        }
        echo PHP_EOL;
    }

}
