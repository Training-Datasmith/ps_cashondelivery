<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();

class HookPaymentOptionsTest extends PsCodTestCase
{
    public function testMissingCartReturnsNoOptions()
    {
        $module = $this->createModule();
        $result = $module->hookPaymentOptions(array());

        $this->assertSame(array(), $result);
    }

    public function testNullCartReturnsNoOptions()
    {
        $module = $this->createModule();
        $result = $module->hookPaymentOptions(array('cart' => null));

        $this->assertSame(array(), $result);
    }

    public function testVirtualCartReturnsNoOptions()
    {
        $module = $this->createModule();
        $cart = $this->createPhysicalCart();
        $cart->virtual = true;

        $result = $module->hookPaymentOptions(array('cart' => $cart));

        $this->assertSame(array(), $result);
    }

    public function testPhysicalCartReturnsOneCashOption()
    {
        $module = $this->createModule();
        $cart = $this->createPhysicalCart();
        $context = Context::getContext();
        $context->cart = $cart;

        $result = $module->hookPaymentOptions(array('cart' => $cart));

        $this->assertCount(1, $result);
        $this->assertInstanceOf('PrestaShop\\PrestaShop\\Core\\Payment\\PaymentOption', $result[0]);
        $this->assertSame('ps_cashondelivery', $result[0]->getModuleName());
        $this->assertSame('Pay by Cash on Delivery', $result[0]->getCallToActionText());

        $domains = array();
        foreach (Module::$transCalls as $call) {
            if ('Pay by Cash on Delivery' === $call['id']) {
                $domains[] = $call['domain'];
            }
        }
        $this->assertContains('Modules.Cashondelivery.Shop', $domains);

        $this->assertNotEmpty(Link::$moduleLinkCalls);
        $linkCall = Link::$moduleLinkCalls[0];
        $this->assertSame('ps_cashondelivery', $linkCall['module']);
        $this->assertSame('validation', $linkCall['controller']);
        $this->assertSame(array(), $linkCall['params']);
        $this->assertTrue($linkCall['ssl']);
        $this->assertSame('MODULE:ps_cashondelivery/validation', $result[0]->getAction());

        $this->assertSame(
            'TPL:module:ps_cashondelivery/views/templates/hook/paymentOptions-additionalInformation.tpl',
            $result[0]->getAdditionalInformation()
        );
    }
}
