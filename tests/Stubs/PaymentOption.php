<?php

namespace PrestaShop\PrestaShop\Core\Payment;

class PaymentOption
{
    /** @var string */
    private $moduleName;

    /** @var string */
    private $callToActionText;

    /** @var string */
    private $action;

    /** @var string */
    private $additionalInformation;

    /**
     * @param string $moduleName
     *
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setCallToActionText($text)
    {
        $this->callToActionText = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getCallToActionText()
    {
        return $this->callToActionText;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $info
     *
     * @return $this
     */
    public function setAdditionalInformation($info)
    {
        $this->additionalInformation = $info;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }
}
