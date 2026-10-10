<?php

class PsCodTestRedirect extends Exception
{
    /** @var string */
    public $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        parent::__construct('Redirect: ' . $url);
        $this->url = $url;
    }
}

class Validate
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isUnsignedInt($value)
    {
        return (is_numeric($value) || is_string($value))
            && (string) (int) $value === (string) $value
            && $value < 4294967296
            && $value >= 0;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public static function isLoadedObject($object)
    {
        return is_object($object) && !empty($object->id);
    }
}

class Tools
{
    /** @var bool|null */
    public static $copyResult;

    /**
     * @param string $str
     *
     * @return string
     */
    public static function strtolower($str)
    {
        return strtolower($str);
    }

    /**
     * @param string $url
     */
    public static function redirect($url)
    {
        throw new PsCodTestRedirect($url);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function file_exists_cache($path)
    {
        return file_exists($path);
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    public static function copy($source, $destination)
    {
        if (null !== self::$copyResult) {
            return self::$copyResult;
        }

        return copy($source, $destination);
    }

    public static function reset()
    {
        self::$copyResult = null;
    }
}

class Configuration
{
    /** @var array<string, mixed> */
    public static $data = array();

    /** @var bool */
    public static $updateGlobalValueResult = true;

    /** @var array<int, array<string, mixed>> */
    public static $updateGlobalValueCalls = array();

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : null;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function getGlobalValue($key)
    {
        return self::get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function updateGlobalValue($key, $value)
    {
        self::$updateGlobalValueCalls[] = array(
            'key' => $key,
            'value' => $value,
        );

        if (false === self::$updateGlobalValueResult) {
            return false;
        }

        self::$data[$key] = $value;

        return true;
    }

    public static function reset()
    {
        self::$data = array();
        self::$updateGlobalValueResult = true;
        self::$updateGlobalValueCalls = array();
    }
}

class Language
{
    /** @var array<int, array<string, mixed>> */
    public static $languages = array();

    /**
     * @param bool $active
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getLanguages($active)
    {
        return self::$languages;
    }

    public static function reset()
    {
        self::$languages = array();
    }
}

class Currency
{
    /** @var array<int, string> */
    public static $isoById = array();

    /** @var int */
    public $id;

    /** @var string */
    public $iso_code;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = (int) $id;
        $this->iso_code = isset(self::$isoById[$this->id]) ? self::$isoById[$this->id] : '';
    }

    public static function reset()
    {
        self::$isoById = array();
    }
}

class Shop
{
    /** @var string */
    public $name = 'Cod Shop';
}

class Link
{
    /** @var array<int, array<string, mixed>> */
    public static $pageLinkCalls = array();

    /** @var array<int, array<string, mixed>> */
    public static $moduleLinkCalls = array();

    /**
     * @param string $controller
     * @param bool   $ssl
     * @param int    $idLang
     * @param array  $params
     *
     * @return string
     */
    public function getPageLink($controller, $ssl = null, $idLang = null, $params = array())
    {
        self::$pageLinkCalls[] = array(
            'controller' => $controller,
            'ssl' => $ssl,
            'idLang' => $idLang,
            'params' => $params,
        );

        return 'PAGE:' . $controller . ':' . json_encode($params);
    }

    /**
     * @param string $module
     * @param string $controller
     * @param array  $params
     * @param bool   $ssl
     *
     * @return string
     */
    public function getModuleLink($module, $controller, $params = array(), $ssl = null)
    {
        self::$moduleLinkCalls[] = array(
            'module' => $module,
            'controller' => $controller,
            'params' => $params,
            'ssl' => $ssl,
        );

        return 'MODULE:' . $module . '/' . $controller;
    }

    public static function reset()
    {
        self::$pageLinkCalls = array();
        self::$moduleLinkCalls = array();
    }
}

class LocaleFormatter
{
    /** @var array<int, array<string, mixed>> */
    public static $formatPriceCalls = array();

    /**
     * @param float  $amount
     * @param string $iso
     *
     * @return string
     */
    public function formatPrice($amount, $iso)
    {
        self::$formatPriceCalls[] = array('amount' => $amount, 'iso' => $iso);

        return $amount . ' ' . $iso;
    }

    public static function reset()
    {
        self::$formatPriceCalls = array();
    }
}

class Smarty
{
    /** @var array<string, mixed> */
    public $assigned = array();

    /**
     * @param array<string, mixed> $tplVars
     */
    public function assign($tplVars)
    {
        $this->assigned = array_merge($this->assigned, $tplVars);
    }

    public function reset()
    {
        $this->assigned = array();
    }
}

class Context
{
    /** @var Context|null */
    private static $instance;

    /** @var Cart|null */
    public $cart;

    /** @var Link */
    public $link;

    /** @var Smarty */
    public $smarty;

    /** @var Shop */
    public $shop;

    /** @var object */
    public $language;

    /** @var object */
    public $currency;

    /** @var LocaleFormatter */
    private $locale;

    private function __construct()
    {
        $this->link = new Link();
        $this->smarty = new Smarty();
        $this->shop = new Shop();
        $this->language = (object) array('id' => 2);
        $this->currency = (object) array('id' => 4);
        $this->locale = new LocaleFormatter();
    }

    /**
     * @return Context
     */
    public static function getContext()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return LocaleFormatter
     */
    public function getCurrentLocale()
    {
        return $this->locale;
    }

    public static function reset()
    {
        self::$instance = null;
    }
}

class Cart
{
    const BOTH = 3;

    /** @var array<int, array<string, mixed>> */
    public static $getOrderTotalCalls = array();

    /** @var int */
    public $id;

    /** @var int */
    public $id_customer;

    /** @var int */
    public $id_address_delivery;

    /** @var int */
    public $id_address_invoice;

    /** @var bool */
    public $virtual = false;

    /**
     * @param int $id
     */
    public function __construct($id = 0)
    {
        $this->id = (int) $id;
    }

    /**
     * @return bool
     */
    public function isVirtualCart()
    {
        return $this->virtual;
    }

    /**
     * @param bool   $withTaxes
     * @param int    $type
     *
     * @return float
     */
    public function getOrderTotal($withTaxes, $type)
    {
        self::$getOrderTotalCalls[] = array(
            'withTaxes' => $withTaxes,
            'type' => $type,
        );

        return 20.5;
    }

    public static function reset()
    {
        self::$getOrderTotalCalls = array();
    }
}

class Customer
{
    /** @var array<int, true> */
    public static $loadedIds = array();

    /** @var int */
    public $id;

    /** @var string */
    public $secure_key = 'abcdef0123456789';

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $id = (int) $id;
        if (isset(self::$loadedIds[$id])) {
            $this->id = $id;
        } else {
            $this->id = 0;
        }
    }

    public static function reset()
    {
        self::$loadedIds = array();
    }
}

class Order
{
    /** @var int */
    public $id;

    /** @var string */
    public $module;

    /** @var string */
    public $reference;

    /** @var int */
    public $id_currency;

    /** @var float */
    private $totalPaid;

    /**
     * @param int $id
     */
    public function __construct($id = 0)
    {
        $this->id = (int) $id;
        $this->totalPaid = 12.5;
        $this->id_currency = 3;
        $this->reference = 'XKBKNABJK';
        $this->module = 'ps_cashondelivery';
    }

    /**
     * @param float $total
     */
    public function setTotalPaid($total)
    {
        $this->totalPaid = (float) $total;
    }

    /**
     * @return float
     */
    public function getOrdersTotalPaid()
    {
        return $this->totalPaid;
    }
}

class OrderState
{
    /** @var array<int, OrderState> */
    public static $instances = array();

    /** @var int */
    public static $nextId = 1;

    /** @var bool */
    public static $addResult = true;

    /** @var int */
    public static $addCallCount = 0;

    /** @var int */
    public $id;

    /** @var string */
    public $module_name;

    /** @var array<int, string>|string */
    public $name;

    /** @var string */
    public $color;

    /** @var bool */
    public $logable;

    /** @var bool */
    public $paid;

    /** @var bool */
    public $invoice;

    /** @var bool */
    public $shipped;

    /** @var bool */
    public $delivery;

    /** @var bool */
    public $pdf_delivery;

    /** @var bool */
    public $pdf_invoice;

    /** @var bool */
    public $send_email;

    /** @var bool */
    public $hidden;

    /** @var bool */
    public $unremovable;

    /** @var string */
    public $template;

    /** @var bool */
    public $deleted;

    /**
     * @param int $id
     */
    public function __construct($id = 0)
    {
        $id = (int) $id;
        if ($id > 0 && isset(self::$instances[$id])) {
            $this->id = $id;
            $stored = self::$instances[$id];
            foreach (get_object_vars($stored) as $key => $value) {
                $this->$key = $value;
            }
        } else {
            $this->id = 0;
        }
    }

    /**
     * @return bool
     */
    public function add()
    {
        ++self::$addCallCount;

        if (false === self::$addResult) {
            return false;
        }

        if ($this->id <= 0) {
            $this->id = self::$nextId++;
        }

        self::$instances[$this->id] = clone $this;

        return true;
    }

    public static function reset()
    {
        self::$instances = array();
        self::$nextId = 1;
        self::$addResult = true;
        self::$addCallCount = 0;
    }

    /**
     * @return int
     */
    public static function countAdded()
    {
        return count(self::$instances);
    }
}

class Module
{
    /** @var bool */
    public static $installResult = true;

    /** @var bool */
    public static $registerHookResult = true;

    /** @var array<int, string> */
    public static $registeredHooks = array();

    /** @var array<int, array<string, string>> */
    public static $hookCalls = array();

    /** @var array<int, array<string, string>> */
    public static $paymentModules = array();

    /** @var array<int, array<string, string>> */
    public static $transCalls = array();

    /** @var string */
    public $name;

    /** @var Context */
    public $context;

    /** @var array<int, string> */
    public $_errors = array();

    /** @var int */
    public $id = 12;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * @return bool
     */
    public function install()
    {
        return self::$installResult;
    }

    /**
     * @param string|array $hookName
     *
     * @return bool
     */
    public function registerHook($hookName)
    {
        self::$hookCalls[] = array('action' => 'registerHook', 'hook' => is_array($hookName) ? implode(',', $hookName) : $hookName);
        if (false === self::$registerHookResult) {
            return false;
        }
        if (is_array($hookName)) {
            foreach ($hookName as $hook) {
                self::$registeredHooks[] = $hook;
            }
        } else {
            self::$registeredHooks[] = $hookName;
        }

        return true;
    }

    /**
     * @param string $hookName
     *
     * @return bool
     */
    public function unregisterHook($hookName)
    {
        self::$hookCalls[] = array('action' => 'unregisterHook', 'hook' => $hookName);
        self::$registeredHooks = array_values(array_filter(self::$registeredHooks, function ($hook) use ($hookName) {
            return $hook !== $hookName;
        }));

        return true;
    }

    /**
     * @param string $hookName
     *
     * @return bool
     */
    public function isRegisteredInHook($hookName)
    {
        return in_array($hookName, self::$registeredHooks, true);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getPaymentModules()
    {
        return self::$paymentModules;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    public function fetch($template)
    {
        return 'TPL:' . $template;
    }

    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    public function trans($id, $parameters = array(), $domain = null)
    {
        self::$transCalls[] = array('id' => $id, 'domain' => $domain);

        return $id;
    }

    /**
     * @return string
     */
    public function getLocalPath()
    {
        return dirname(dirname(__DIR__)) . '/';
    }

    public static function reset()
    {
        self::$installResult = true;
        self::$registerHookResult = true;
        self::$registeredHooks = array();
        self::$hookCalls = array();
        self::$paymentModules = array();
        self::$transCalls = array();
    }
}

class PaymentModule extends Module
{
    /** @var int */
    public $currentOrder;

    /** @var array<int, array<string, mixed>> */
    public static $validateOrderCalls = array();

    /**
     * @param int         $idCart
     * @param int         $idOrderState
     * @param float       $amountPaid
     * @param string      $paymentMethod
     * @param string|null $message
     * @param array       $extraVars
     * @param int         $currencySpecial
     * @param bool        $dontTouchAmount
     * @param string      $secureKey
     */
    public function validateOrder(
        $idCart,
        $idOrderState,
        $amountPaid,
        $paymentMethod,
        $message,
        $extraVars,
        $currencySpecial,
        $dontTouchAmount,
        $secureKey
    ) {
        self::$validateOrderCalls[] = array(
            'idCart' => $idCart,
            'idOrderState' => $idOrderState,
            'amountPaid' => $amountPaid,
            'paymentMethod' => $paymentMethod,
            'message' => $message,
            'extraVars' => $extraVars,
            'currencySpecial' => $currencySpecial,
            'dontTouchAmount' => $dontTouchAmount,
            'secureKey' => $secureKey,
        );
        $this->currentOrder = 77;
    }

    public static function reset()
    {
        parent::reset();
        self::$validateOrderCalls = array();
    }
}

class ModuleFrontController
{
    /** @var bool */
    public $ssl = false;

    /** @var PaymentModule */
    public $module;

    /** @var Context */
    public $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }
}
