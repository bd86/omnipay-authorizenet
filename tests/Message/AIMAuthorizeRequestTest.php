<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Tests\TestCase;

class AIMAuthorizeRequestTest extends TestCase
{
    /** @var AIMAuthorizeRequest */
    protected $request;

    public function setUp()
    {
        $this->request = new AIMAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'clientIp' => '10.0.0.1',
                'amount' => '12.00',
                'customerId' => 'cust-id',
                'card' => $this->getValidCard(),
                'duplicateWindow' => 0,
                'description' => 'Product Description'
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals('authOnlyTransaction', $data->transactionRequest->transactionType);
        $this->assertEquals('10.0.0.1', $data->transactionRequest->customerIP);
        $this->assertEquals('cust-id', $data->transactionRequest->customer->id);

        // Issue #38 Make sure the transactionRequest properties are correctly ordered.
        // This feels messy, but works.
        $transactionRequestProperties = array_keys(get_object_vars($data->transactionRequest));
        // The names of the properies of the $data->transactionRequest object, in the order in
        // which they must be defined for Authorize.Net to accept the transaction.
        $keys = array(
            "transactionType",
            "amount",
            "description",
            "payment",
            "customer",
            "billTo",
            "shipTo",
            "customerIP",
            "transactionSettings"
        );
        $this->assertEquals($keys, $transactionRequestProperties);

        $setting = $data->transactionRequest->transactionSettings->setting[0];
        $this->assertEquals('testRequest', $setting->settingName);
        $this->assertEquals('false', $setting->settingValue);
    }

    public function testGetDataTestMode()
    {
        $this->request->setTestMode(true);

        $data = $this->request->getData();

        $setting = $data->transactionRequest->transactionSettings->setting[0];
        $this->assertEquals('testRequest', $setting->settingName);
        $this->assertEquals('true', $setting->settingValue);
    }

    public function testShouldIncludeDuplicateWindowSetting()
    {
        $data = $this->request->getData();
        $setting = $data->transactionRequest->transactionSettings->setting[1];
        $this->assertEquals('duplicateWindow', $setting->settingName);
        $this->assertEquals('0', $setting->settingValue);
    }
}
