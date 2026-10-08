<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();
require_once dirname(__DIR__) . '/Support/UpgradeHookModules.php';

class UpgradeModuleTest extends PsCodTestCase
{
    public function testUpgrade107OnCurrentModuleUnregistersLegacyHook()
    {
        $module = $this->createModule();

        $this->assertTrue(upgrade_module_1_0_7($module));
        $this->assertSame(
            array(array('action' => 'unregisterHook', 'hook' => 'paymentReturn')),
            Module::$hookCalls
        );
    }

    public function testUpgrade107RegistersDisplayPaymentReturnWhenMethodExists()
    {
        $module = new PsCodUpgradeHookMethodModule();

        $this->assertTrue(upgrade_module_1_0_7($module));
        $this->assertSame(
            array(
                array('action' => 'unregisterHook', 'hook' => 'paymentReturn'),
                array('action' => 'registerHook', 'hook' => 'displayPaymentReturn'),
            ),
            Module::$hookCalls
        );
    }

    public function testUpgrade107RegistersDisplayPaymentReturnForWidget()
    {
        $module = new PsCodUpgradeWidgetModule();

        $this->assertTrue(upgrade_module_1_0_7($module));
        $this->assertContains(
            array('action' => 'registerHook', 'hook' => 'displayPaymentReturn'),
            Module::$hookCalls
        );
    }

    public function testUpgrade200CreatesStateAndSwapsHooks()
    {
        Module::$registeredHooks = array('displayPaymentReturn');
        Configuration::$data = array();

        $module = $this->createModule();
        $this->assertTrue(upgrade_module_2_0_0($module));

        $this->assertGreaterThan(0, OrderState::countAdded());
        $this->assertTrue($module->isRegisteredInHook('displayOrderConfirmation'));
        $this->assertTrue($module->isRegisteredInHook('paymentOptions'));
        $this->assertFalse($module->isRegisteredInHook('displayPaymentReturn'));
    }

    public function testUpgrade200KeepsAnExistingState()
    {
        $existing = new OrderState();
        $existing->id = 9;
        $existing->module_name = 'ps_cashondelivery';
        $existing->name = array(1 => 'Existing');
        OrderState::$instances[9] = $existing;
        Configuration::$data['PS_OS_COD_VALIDATION'] = 9;

        $module = $this->createModule();
        $this->assertTrue(upgrade_module_2_0_0($module));
        $this->assertSame(0, OrderState::$addCallCount);
    }

    public function testUpgrade200LeavesHooksAlreadyInPlace()
    {
        Module::$registeredHooks = array('displayOrderConfirmation', 'paymentOptions');
        Configuration::$data['PS_OS_COD_VALIDATION'] = 9;
        $existing = new OrderState();
        $existing->id = 9;
        $existing->module_name = 'ps_cashondelivery';
        OrderState::$instances[9] = $existing;

        $module = $this->createModule();
        Module::$hookCalls = array();
        $this->assertTrue(upgrade_module_2_0_0($module));
        $this->assertSame(array(), Module::$hookCalls);
    }
}
