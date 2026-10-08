<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();

class InstallTest extends PsCodTestCase
{
    public function testInstallRegistersHooksAndCreatesOrderState()
    {
        Module::$installResult = true;
        Module::$registerHookResult = true;

        $module = $this->createModule();
        $result = $module->install();

        $this->assertTrue($result);
        $this->assertGreaterThan(0, OrderState::countAdded());
        $this->assertArrayHasKey('PS_OS_COD_VALIDATION', Configuration::$data);

        $registered = false;
        foreach (Module::$hookCalls as $call) {
            if ('registerHook' === $call['action']
                && false !== strpos($call['hook'], 'displayOrderConfirmation')
                && false !== strpos($call['hook'], 'paymentOptions')) {
                $registered = true;
            }
        }
        $this->assertTrue($registered);
    }

    public function testInstallStopsWhenParentInstallFails()
    {
        Module::$installResult = false;

        $module = $this->createModule();
        $result = $module->install();

        $this->assertFalse($result);
        $this->assertSame(0, OrderState::countAdded());
        $this->assertSame(0, count(Module::$hookCalls));
    }

    public function testInstallStopsWhenRegisterHookFails()
    {
        Module::$installResult = true;
        Module::$registerHookResult = false;

        $module = $this->createModule();
        $result = $module->install();

        $this->assertFalse($result);
        $this->assertSame(0, OrderState::countAdded());
    }
}
