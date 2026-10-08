<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();

class HookDisplayOrderConfirmationTest extends PsCodTestCase
{
    public function testUnloadedOrderReturnsEmptyString()
    {
        $module = $this->createModule();
        $order = new Order(0);

        $result = $module->hookDisplayOrderConfirmation(array('order' => $order));

        $this->assertSame('', $result);
        $this->assertSame(array(), Context::getContext()->smarty->assigned);
    }

    public function testOtherModuleReturnsEmptyString()
    {
        $module = $this->createModule();
        $order = new Order(1);
        $order->module = 'cheque';

        $result = $module->hookDisplayOrderConfirmation(array('order' => $order));

        $this->assertSame('', $result);
    }

    public function testMatchingOrderAssignsConfirmationData()
    {
        $module = $this->createModule();
        $order = new Order(1);
        $order->module = 'ps_cashondelivery';
        $order->reference = 'XKBKNABJK';
        $order->setTotalPaid(12.5);
        $order->id_currency = 3;
        Currency::$isoById[3] = 'EUR';

        $result = $module->hookDisplayOrderConfirmation(array('order' => $order));
        $assigned = Context::getContext()->smarty->assigned;

        $this->assertSame(
            'TPL:module:ps_cashondelivery/views/templates/hook/displayOrderConfirmation.tpl',
            $result
        );
        $this->assertSame('Cod Shop', $assigned['shop_name']);
        $this->assertSame('XKBKNABJK', $assigned['reference']);
        $this->assertSame('12.5 EUR', $assigned['total']);
        $this->assertContains('PAGE:contact:', $assigned['contact_url']);

        $this->assertCount(1, LocaleFormatter::$formatPriceCalls);
        $this->assertSame(12.5, LocaleFormatter::$formatPriceCalls[0]['amount']);
        $this->assertSame('EUR', LocaleFormatter::$formatPriceCalls[0]['iso']);
    }

    public function testObjOrderWinsOverOrderForTotal()
    {
        $module = $this->createModule();
        $objOrder = new Order(2);
        $objOrder->module = 'ps_cashondelivery';
        $objOrder->reference = 'OBJREF';
        $objOrder->setTotalPaid(12.5);
        $objOrder->id_currency = 3;
        Currency::$isoById[3] = 'EUR';

        $order = new Order(3);
        $order->module = 'ps_cashondelivery';
        $order->reference = 'ORDREF';
        $order->setTotalPaid(99.0);
        $order->id_currency = 4;
        Currency::$isoById[4] = 'USD';

        $module->hookDisplayOrderConfirmation(array('objOrder' => $objOrder, 'order' => $order));
        $assigned = Context::getContext()->smarty->assigned;

        $this->assertSame('OBJREF', $assigned['reference']);
        $this->assertSame('12.5 EUR', $assigned['total']);
    }

    public function testObjOrderAloneRenders()
    {
        $module = $this->createModule();
        $objOrder = new Order(2);
        $objOrder->module = 'ps_cashondelivery';
        $objOrder->reference = 'XKBKNABJK';
        $objOrder->setTotalPaid(12.5);
        $objOrder->id_currency = 3;
        Currency::$isoById[3] = 'EUR';

        $result = $module->hookDisplayOrderConfirmation(array('objOrder' => $objOrder));

        $this->assertContains('displayOrderConfirmation.tpl', $result);
        $this->assertSame('12.5 EUR', Context::getContext()->smarty->assigned['total']);
    }

    public function testObjOrderNotLoadedDoesNotFallThrough()
    {
        $module = $this->createModule();
        $objOrder = new Order(0);
        $order = new Order(1);
        $order->module = 'ps_cashondelivery';

        $result = $module->hookDisplayOrderConfirmation(array('objOrder' => $objOrder, 'order' => $order));

        $this->assertSame('', $result);
    }

    public function testNeitherOrderKeyReturnsEmptyString()
    {
        $module = $this->createModule();

        $result = $module->hookDisplayOrderConfirmation(array());

        $this->assertSame('', $result);
    }
}
