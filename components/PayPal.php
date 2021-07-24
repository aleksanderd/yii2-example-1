<?php

namespace app\components;

use flyiing\helpers\Html;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use Yii;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use yii\base\Component;
use yii\helpers\Url;

/**
 * Class PayPal
 *
 * @property \PayPal\Rest\ApiContext $context
 */
class PayPal extends Component
{
    public $mode;
    public $client_id;
    public $client_secret;

    public $merchant_id;

    private $apiContext;

    function init()
    {
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_id, $this->client_secret));
        $this->apiContext->setConfig([
            'mode' => $this->mode,
            'log.LogEnabled' => true,
            'log.FileName' => \Yii::getAlias('@runtime/logs/paypal.log'),
            'log.LogLevel' => 'FINE',
        ]);
        if (!isset($this->merchant_id)) {
            $this->merchant_id = 'gmcf';
        }
    }

    public function getContext()
    {
        return $this->apiContext;
    }

    public function getPlan($name = 'GMCF 100')
    {
        if (preg_match('/GMCF (\d+)/', $name, $m)) {
            $amount = $m[1];
        } else {
            $amount = '100';
        }

        $plansList = Plan::all(['status' => 'ACTIVE', 'name' => $name], $this->apiContext);
        $plans = $plansList->getPlans();

        if (is_array($plans)) {
            foreach ($plans as $plan) {
//                $plan = Plan::get($plan->getId(), $this->apiContext);
//                echo Html::tag('pre', print_r($plan, true));
                return $plan;
                //$plan->delete($this->apiContext);
            }
        }

        $plan = new Plan();
        $plan->setName($name)
            ->setDescription('GMCF plan description')
            ->setType('INFINITE');

        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular payments')
            ->setType('REGULAR')
            ->setFrequency('DAY')
            ->setFrequencyInterval('1')
            ->setCycles('0')
            ->setAmount(new Currency([
                'value' => 100,
                'currency' => Yii::$app->currencyCode
            ]));

        // доп. цена за доставку или налог
//        $chargeModel = new ChargeModel();
//        $chargeModel->setType('SHIPPING')
//            ->setAmount(new Currency(['value' => 10, 'currency' => Yii::$app->currencyCode]));
//        $paymentDefinition->setChargeModels([$chargeModel]);

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(Url::to(['/payment/paypal-regular', 'success' => 1], true))
            ->setCancelUrl(Url::to(['/payment/paypal-regular', 'success' => 0], true))
            ->setAutoBillAmount('YES')
            ->setInitialFailAmountAction('CONTINUE');

        $plan->setPaymentDefinitions([$paymentDefinition]);
        $plan->setMerchantPreferences($merchantPreferences);

        $plan->create($this->apiContext);

        $pPatch = new Patch();
        $pPatch->setOp('replace')
            ->setPath('/')
            ->setValue(new PayPalModel('{"state": "ACTIVE"}'));
        $pPatchRequest = new PatchRequest();
        $pPatchRequest->addPatch($pPatch);

        $plan->update($pPatchRequest, $this->apiContext);
        $plan = Plan::get($plan->getId(), $this->apiContext);

        echo Html::tag('pre', print_r($plan, true));

        return $plan;
    }

}
