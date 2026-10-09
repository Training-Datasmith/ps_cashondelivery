<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();
require_once dirname(__DIR__) . '/Support/MissingIconPathModule.php';

class InstallOrderStateTest extends PsCodTestCase
{
    public function testNamesFollowLanguageIsoInsteadOfEnglishOverwrite()
    {
        $module = $this->createModule();
        $this->assertTrue($module->installOrderState());

        $state = new OrderState((int) Configuration::get('PS_OS_COD_VALIDATION'));
        $names = $state->name;

        $this->assertSame('En attente de paiement à la livraison', $names[1]);
        $this->assertSame('Warten auf Zahlungseingang Nachnahme', $names[2]);
        $this->assertSame('代金引換払い確認待ち', $names[3]);
        $this->assertSame('Awaiting Cash On Delivery validation', $names[4]);
        $this->assertSame('배송시 현금 지불 확인 대기', $names[5]);
        $this->assertSame('Awaiting Cash On Delivery validation', $names[6]);
    }

    public function testNewOrderStateIsUnpaidAndUnremovable()
    {
        $module = $this->createModule();
        $module->installOrderState();

        $state = new OrderState((int) Configuration::get('PS_OS_COD_VALIDATION'));

        $this->assertSame('ps_cashondelivery', $state->module_name);
        $this->assertFalse($state->logable);
        $this->assertFalse($state->paid);
        $this->assertFalse($state->invoice);
        $this->assertFalse($state->shipped);
        $this->assertFalse($state->delivery);
        $this->assertFalse($state->pdf_delivery);
        $this->assertFalse($state->pdf_invoice);
        $this->assertFalse($state->send_email);
        $this->assertFalse($state->hidden);
        $this->assertFalse($state->deleted);
        $this->assertTrue($state->unremovable);
        $this->assertSame('', $state->template);
    }

    public function testColorDependsOnPrestaShopVersion()
    {
        $module = $this->createModule();
        $module->installOrderState();

        $state = new OrderState((int) Configuration::get('PS_OS_COD_VALIDATION'));
        if ('1.7.7.0' === _PS_VERSION_) {
            $this->assertSame('#4169E1', $state->color);
        } else {
            $this->assertSame('#34219E', $state->color);
        }
    }

    public function testIconIsCopiedFromModuleGif()
    {
        $module = $this->createModule();
        $module->installOrderState();

        $id = (int) Configuration::get('PS_OS_COD_VALIDATION');
        $source = dirname(dirname(__DIR__)) . '/views/img/orderstate/PS_OS_COD_VALIDATION.gif';
        $dest = _PS_ORDER_STATE_IMG_DIR_ . $id . '.gif';

        $this->assertFileExists($source);
        $this->assertFileExists($dest);
        $this->assertSame(sha1_file($source), sha1_file($dest));
    }

    public function testExistingOwnedStateIsLeftAlone()
    {
        $existing = new OrderState();
        $existing->id = 5;
        $existing->module_name = 'ps_cashondelivery';
        $existing->color = '#000000';
        $existing->name = array(1 => 'Keep');
        OrderState::$instances[5] = $existing;
        Configuration::$data['PS_OS_COD_VALIDATION'] = 5;
        OrderState::$nextId = 10;

        $module = $this->createModule();
        $this->assertTrue($module->installOrderState());
        $this->assertSame(0, OrderState::$addCallCount);
        $this->assertSame('#000000', OrderState::$instances[5]->color);
    }

    public function testMissingStateIsRecreated()
    {
        Configuration::$data['PS_OS_COD_VALIDATION'] = 5;

        $module = $this->createModule();
        $module->installOrderState();

        $this->assertNotSame(5, (int) Configuration::get('PS_OS_COD_VALIDATION'));
        $this->assertGreaterThan(0, OrderState::countAdded());
    }

    public function testForeignModuleStateIsRecreated()
    {
        $foreign = new OrderState();
        $foreign->id = 5;
        $foreign->module_name = 'cheque';
        $foreign->color = '#111111';
        $foreign->name = array(1 => 'Cheque');
        OrderState::$instances[5] = $foreign;
        Configuration::$data['PS_OS_COD_VALIDATION'] = 5;

        $module = $this->createModule();
        $module->installOrderState();

        $this->assertNotSame(5, (int) Configuration::get('PS_OS_COD_VALIDATION'));
    }

    public function testAddFailureReportsError()
    {
        OrderState::$addResult = false;

        $module = $this->createModule();
        $this->assertFalse($module->installOrderState());
        $this->assertContains('Failed to create OrderState PS_OS_COD_VALIDATION', $module->_errors);
        $this->assertArrayNotHasKey('PS_OS_COD_VALIDATION', Configuration::$data);
    }

    public function testConfigurationFailureReportsError()
    {
        Configuration::$updateGlobalValueResult = false;

        $module = $this->createModule();
        $this->assertFalse($module->installOrderState());
        $this->assertGreaterThan(0, OrderState::$addCallCount);
        $this->assertArrayNotHasKey('PS_OS_COD_VALIDATION', Configuration::$data);
        $this->assertSame(0, count(glob(_PS_ORDER_STATE_IMG_DIR_ . '*.gif')));
    }

    public function testMissingIconReportsError()
    {
        $module = new Ps_CashondeliveryMissingIconPathModule();
        $this->assertFalse($module->installOrderState());
        $this->assertContains('Failed to find icon file of OrderState PS_OS_COD_VALIDATION', $module->_errors);
    }

    public function testCopyFailureReportsError()
    {
        Tools::$copyResult = false;

        $module = $this->createModule();
        $this->assertFalse($module->installOrderState());
        $this->assertContains('Failed to copy icon of OrderState PS_OS_COD_VALIDATION', $module->_errors);
    }
}
