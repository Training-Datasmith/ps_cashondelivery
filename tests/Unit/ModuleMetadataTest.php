<?php

require_once dirname(__DIR__) . '/TestCase.php';
PsCodTestCase::loadProductionCode();

class ModuleMetadataTest extends PsCodTestCase
{
    public function testModuleIdentity()
    {
        $module = $this->createModule();

        $this->assertSame('ps_cashondelivery', $module->name);
        $this->assertSame('payments_gateways', $module->tab);
        $this->assertSame('2.0.1', $module->version);
        $this->assertSame('PrestaShop', $module->author);
        $this->assertSame(1, $module->need_instance);
        $this->assertFalse($module->currencies);
        $this->assertSame(array('validation'), $module->controllers);
        $this->assertSame(
            array('displayOrderConfirmation', 'paymentOptions'),
            Ps_Cashondelivery::HOOKS
        );
        $this->assertSame('PS_OS_COD_VALIDATION', Ps_Cashondelivery::CONFIG_OS_CASH_ON_DELIVERY);
        $this->assertSame('1.7.6.0', $module->ps_versions_compliancy['min']);
        $this->assertSame(_PS_VERSION_, $module->ps_versions_compliancy['max']);
    }

    public function testDisplayNameAndDescriptionUseAdminTranslations()
    {
        $module = $this->createModule();

        $this->assertSame('Cash on delivery (COD)', $module->displayName);
        $this->assertSame(
            'Accept cash payments on delivery to make it easy for customers to purchase on your store.',
            $module->description
        );

        $domains = array();
        foreach (Module::$transCalls as $call) {
            $domains[] = $call['domain'];
        }
        $this->assertContains('Modules.Cashondelivery.Admin', $domains);
    }

    public function testConfigXmlMatchesModuleVersion()
    {
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/config.xml');

        $this->assertContains('<version><![CDATA[2.0.1]]></version>', $xml);
        $this->assertContains('<name>ps_cashondelivery</name>', $xml);
    }

    public function testTemplatesExposeAssignedAnchors()
    {
        $additional = file_get_contents(
            dirname(dirname(__DIR__)) . '/views/templates/hook/paymentOptions-additionalInformation.tpl'
        );
        $confirmation = file_get_contents(
            dirname(dirname(__DIR__)) . '/views/templates/hook/displayOrderConfirmation.tpl'
        );

        $this->assertContains('id="ps_cashondelivery-paymentOptions-additionalInformation"', $additional);
        $this->assertContains('id="ps_cashondelivery-displayOrderConfirmation"', $confirmation);
        $this->assertContains('$shop_name', $confirmation);
        $this->assertContains('{$contact_url}', $confirmation);
    }
}
