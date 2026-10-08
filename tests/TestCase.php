<?php

abstract class PsCodTestCase extends PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $orderStateImgDir;

    /** @var bool */
    private static $productionLoaded = false;

    public static function loadProductionCode()
    {
        if (self::$productionLoaded) {
            return;
        }

        require_once dirname(__DIR__) . '/ps_cashondelivery.php';
        require_once dirname(__DIR__) . '/controllers/front/validation.php';
        require_once dirname(__DIR__) . '/upgrade/upgrade-1.0.7.php';
        require_once dirname(__DIR__) . '/upgrade/upgrade-2.0.0.php';

        self::$productionLoaded = true;
    }

    protected function setUp()
    {
        self::loadProductionCode();
        parent::setUp();
        Context::reset();
        Configuration::reset();
        OrderState::reset();
        Currency::reset();
        Customer::reset();
        Language::reset();
        Module::reset();
        PaymentModule::reset();
        Link::reset();
        LocaleFormatter::reset();
        Tools::reset();

        $this->orderStateImgDir = _PS_ORDER_STATE_IMG_DIR_;
        if (!is_dir($this->orderStateImgDir)) {
            mkdir($this->orderStateImgDir, 0777, true);
        }
        foreach (glob($this->orderStateImgDir . '*.gif') as $file) {
            unlink($file);
        }

        Language::$languages = array(
            array('id_lang' => 1, 'iso_code' => 'fr'),
            array('id_lang' => 2, 'iso_code' => 'de'),
            array('id_lang' => 3, 'iso_code' => 'ja'),
            array('id_lang' => 4, 'iso_code' => 'en'),
            array('id_lang' => 5, 'iso_code' => 'KO'),
            array('id_lang' => 6, 'iso_code' => 'xx'),
        );

        Currency::$isoById[3] = 'EUR';
        Currency::$isoById[4] = 'USD';

        Customer::$loadedIds[8] = true;
    }

    /**
     * @return Ps_Cashondelivery
     */
    protected function createModule()
    {
        return new Ps_Cashondelivery();
    }

    /**
     * @return Cart
     */
    protected function createPhysicalCart()
    {
        $cart = new Cart(10);
        $cart->id_customer = 8;
        $cart->id_address_delivery = 21;
        $cart->id_address_invoice = 22;
        $cart->virtual = false;

        return $cart;
    }
}
