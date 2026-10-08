<?php

class Ps_CashondeliveryMissingIconPathModule extends Ps_Cashondelivery
{
    /** @var string */
    private $emptyPath;

    public function __construct()
    {
        parent::__construct();
        $this->emptyPath = sys_get_temp_dir() . '/ps_cod_empty_' . uniqid() . '/';
        mkdir($this->emptyPath, 0777, true);
    }

    public function getLocalPath()
    {
        return $this->emptyPath;
    }
}
