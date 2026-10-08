<?php

class PsCodUpgradeHookMethodModule extends Ps_Cashondelivery
{
    public function hookDisplayPaymentReturn()
    {
        return '';
    }
}

class PsCodUpgradeWidgetModule extends Ps_Cashondelivery implements \PrestaShop\PrestaShop\Core\Module\WidgetInterface
{
}
