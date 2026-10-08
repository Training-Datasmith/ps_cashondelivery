<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();

class ValidationControllerTest extends PsCodTestCase
{
    /** @var Ps_CashondeliveryValidationModuleFrontController */
    private $controller;

    /** @var Ps_Cashondelivery */
    private $module;

    protected function setUp()
    {
        parent::setUp();

        Configuration::$data['PS_OS_COD_VALIDATION'] = 42;
        Module::$paymentModules = array(array('name' => 'ps_cashondelivery'));

        $this->module = $this->createModule();
        $this->controller = new Ps_CashondeliveryValidationModuleFrontController();
        $this->controller->module = $this->module;
        $this->controller->context = Context::getContext();
        $this->controller->context->cart = $this->createPhysicalCart();
        $this->controller->context->cart->id = 100;
    }

    /**
     * @param string $method
     *
     * @return ReflectionMethod
     */
    private function getPrivateMethod($method)
    {
        $reflection = new ReflectionMethod('Ps_CashondeliveryValidationModuleFrontController', $method);
        $reflection->setAccessible(true);

        return $reflection;
    }

    public function testSslIsRequired()
    {
        $this->assertTrue($this->controller->ssl);
    }

    public function testContextAcceptsACompletePhysicalCart()
    {
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->assertTrue($method->invoke($this->controller));
    }

    public function testContextRejectsUnloadedCart()
    {
        $this->controller->context->cart = new Cart(0);
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testContextRejectsNullCart()
    {
        $this->controller->context->cart = null;
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testContextRejectsBadCustomerId()
    {
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->controller->context->cart->id_customer = -1;
        $this->assertFalse($method->invoke($this->controller));
        $this->controller->context->cart->id_customer = 'abc';
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testContextRejectsFractionalCustomerId()
    {
        $this->controller->context->cart->id_customer = 5.5;
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testContextRejectsBadAddresses()
    {
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->controller->context->cart->id_address_delivery = -5;
        $this->assertFalse($method->invoke($this->controller));
        $this->controller->context->cart->id_address_delivery = 21;
        $this->controller->context->cart->id_address_invoice = -5;
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testContextRejectsVirtualCart()
    {
        $this->controller->context->cart->virtual = true;
        $method = $this->getPrivateMethod('checkIfContextIsValid');
        $this->assertFalse($method->invoke($this->controller));
    }

    public function testPaymentOptionRequiresThisModuleName()
    {
        $method = $this->getPrivateMethod('checkIfPaymentOptionIsAvailable');

        Module::$paymentModules = array();
        $this->assertFalse($method->invoke($this->controller));

        Module::$paymentModules = array(array('name' => 'cheque'));
        $this->assertFalse($method->invoke($this->controller));

        Module::$paymentModules = array(array('name' => 'ps_cashondelivery_extra'));
        $this->assertFalse($method->invoke($this->controller));

        Module::$paymentModules = array(array('name' => 'Ps_Cashondelivery'));
        $this->assertFalse($method->invoke($this->controller));

        Module::$paymentModules = array(array(), array('name' => 'ps_cashondelivery'));
        $this->assertTrue($method->invoke($this->controller));
    }

    public function testInvalidContextRedirectsToCheckoutStep()
    {
        $this->controller->context->cart = new Cart(0);

        try {
            $this->controller->postProcess();
            $this->fail('Expected redirect');
        } catch (PsCodTestRedirect $redirect) {
            $this->assertContains('PAGE:order:', $redirect->url);
            $this->assertContains('"step":1', $redirect->url);
        }

        $this->assertSame(0, count(PaymentModule::$validateOrderCalls));
    }

    public function testUnavailableModuleRedirectsToCheckoutStep()
    {
        Module::$paymentModules = array(array('name' => 'cheque'));

        try {
            $this->controller->postProcess();
            $this->fail('Expected redirect');
        } catch (PsCodTestRedirect $redirect) {
            $this->assertContains('PAGE:order:', $redirect->url);
        }

        $this->assertSame(0, count(PaymentModule::$validateOrderCalls));
    }

    public function testUnloadedCustomerRedirectsToCheckoutStep()
    {
        Customer::$loadedIds = array();
        $this->controller->context->cart->id_customer = 8;

        try {
            $this->controller->postProcess();
            $this->fail('Expected redirect');
        } catch (PsCodTestRedirect $redirect) {
            $this->assertContains('PAGE:order:', $redirect->url);
        }

        $this->assertSame(0, count(PaymentModule::$validateOrderCalls));
    }

    public function testValidContextValidatesAndRedirectsToConfirmation()
    {
        try {
            $this->controller->postProcess();
            $this->fail('Expected redirect');
        } catch (PsCodTestRedirect $redirect) {
            $this->assertContains('PAGE:order-confirmation:', $redirect->url);
            $this->assertContains('"id_cart":100', $redirect->url);
            $this->assertContains('"id_module":12', $redirect->url);
            $this->assertContains('"id_order":77', $redirect->url);
            $this->assertContains('"key":"abcdef0123456789"', $redirect->url);
        }

        $this->assertCount(1, PaymentModule::$validateOrderCalls);
        $call = PaymentModule::$validateOrderCalls[0];
        $this->assertSame(100, $call['idCart']);
        $this->assertSame(42, $call['idOrderState']);
        $this->assertSame(20.5, $call['amountPaid']);
        $this->assertSame('Cash on delivery (COD)', $call['paymentMethod']);
        $this->assertNull($call['message']);
        $this->assertSame(array(), $call['extraVars']);
        $this->assertSame(4, $call['currencySpecial']);
        $this->assertFalse($call['dontTouchAmount']);
        $this->assertSame('abcdef0123456789', $call['secureKey']);
    }
}
