<?php

namespace app\models\paypal;

use PayPal\Api\Amount;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Transaction as PaypalTransaction;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Exception\PayPalConnectionException;
use Yii;

class CreditCard extends Model
{
    /** @var string Номер карты */
    public $number;
    /** @var string Тип карты: visa, mastercard, discover, amex */
    public $type;
    /** @var integer Месяц срока действия: 1..12 */
    public $expire_month;
    /** @var integer Год срока действия: 4 цифры */
    public $expire_year;
    /** @var string CVV2 */
    public $cvv2;
    /** @var string */
    public $first_name;
    /** @var string */
    public $last_name;
    /** @var string */
    public $billing_address;

    /** @var string */
    public $external_customer_id;
    /** @var string */
    public $merchant_id;
    /** @var string */
    public $external_card_id;

    public function rules()
    {
        $year = date('Y');
        return [
            [['number', 'type', 'expire_month', 'expire_year', 'cvv2', 'first_name', 'last_name'],
                'required'],
            ['expire_month', 'integer', 'min' => 1, 'max' => 12],
            ['expire_year', 'integer', 'min' => $year, 'max' => $year + 10],
            ['number', 'string'],
            ['type', 'string',],
            ['cvv2', 'string', 'min' => 3, 'max' => 4],
            [['first_name', 'last_name', 'billing_address'], 'string', 'max' => 25],
            [['external_customer_id', 'merchant_id', 'external_card_id'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'number' => Yii::t('app', 'Card number'),
            'type' => Yii::t('app', 'Card type'),
            'expire_month' => Yii::t('app', 'Expire month'),
            'expire_year' => Yii::t('app', 'Expire year'),
            'cvv2' => Yii::t('app', 'CVV2'),
            'first_name' => Yii::t('app', 'First name'),
            'last_name' => Yii::t('app', 'Last name'),
            'billing_address' => Yii::t('app', 'Billing address'),
        ];
    }

    /**
     * @param null|\PayPal\Api\CreditCard $result
     * @return null|\PayPal\Api\CreditCard
     */
    public function getApiCreditCard(&$result = null)
    {
        if ($result === null) {
            $result = new \PayPal\Api\CreditCard();
        }
        $result->setNumber($this->number)
            ->setType($this->type)
            ->setExpireMonth($this->expire_month)
            ->setExpireYear($this->expire_year)
            ->setFirstName($this->first_name)
            ->setLastName($this->last_name);
//        if (isset($this->billing_address)) {
//            $result->setBillingAddress($this->billing_address);
//        }
        if (isset($this->merchant_id)) {
            $result->setMerchantId($this->merchant_id);
        }
        if (isset($this->external_customer_id)) {
            $result->setExternalCustomerId($this->external_customer_id);
        }
        if (isset($this->external_card_id)) {
            $result->setExternalCardId($this->external_card_id);
        }
        return $result;
    }

    public function pay()
    {
        /** @var \app\components\PayPal $pp */
        $pp = Yii::$app->paypal;
        $card = $this->getApiCreditCard();
        $fi = new FundingInstrument();
        $fi->setCreditCard($card);

        $total = '2';
        $currency = Yii::$app->currencyCode;
        $currency = 'USD';
        $payer = new Payer();
        $payer->setPaymentMethod('credit_card')->setFundingInstruments([$fi]);
        $item = new Item();
        $item->setName('GMCF auto payment item')
            ->setDescription('GMCF auto payment item description')
            ->setCurrency($currency)
            ->setQuantity('1')
            ->setPrice($total);
        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setCurrency($currency)->setTotal($total);

        $ppTransaction = new PaypalTransaction();
        $ppTransaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('GMCF auto payment');

        $ppPayment = new PaypalPayment();
        $ppPayment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$ppTransaction]);

        try {
            $ppPayment->create($pp->context);
            return true;

        } catch (PayPalConnectionException $e) {

            return $e;

        }
    }

}
