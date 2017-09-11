<?php

namespace Nextform\Config;

use Nextform\Fields\InputField;
use Nextform\Security\Csrf\TokenManager as CsrfTokenManager;

trait CsrfTokenAdapter
{
    /**
     * @var string
     */
    private $csrfFieldName = 'nextform_csrf_token_';

    /**
     * @var boolean
     */
    private $csrfTokenEnabled = false;

    /**
     * @var InputField
     */
    private $csrfTokenField = null;

    /**
     * @var CsrfTokenManager
     */
    private $csrfTokenManager = null;

    /**
     * @param boolean $enabled
     * @return self
     */
    public function enableCsrfToken($enabled)
    {
        $this->csrfTokenEnabled = $enabled;

        if (true == $this->csrfTokenEnabled) {
            $this->csrfTokenManager = new CsrfTokenManager();
            $this->csrfTokenField = new InputField();

            $token = $this->csrfTokenManager->getToken($this->getCsrfTokenFieldNameUid());

            $this->csrfTokenField->setGhost(true);
            $this->csrfTokenField->setAttribute('name', $this->getCsrfTokenFieldName());
            $this->csrfTokenField->setAttribute('value', $token->value);

            $this->addField($this->csrfTokenField);
        } elseif ($this->csrfTokenField instanceof InputField) {
            $this->csrfTokenManager = null;
            $this->removeField($this->csrfTokenField);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCsrfTokenFieldNameUid()
    {
        return Signature::get($this);
    }

    /**
     * @return CsrfTokenManager
     */
    public function getCsrfTokenManager()
    {
        return $this->csrfTokenManager;
    }

    /**
     * @return string
     */
    public function getCsrfTokenFieldName()
    {
        return $this->csrfFieldName . $this->getCsrfTokenFieldNameUid();
    }

    /**
     * @return InputField
     */
    public function getCsrfTokenField()
    {
        return $this->csrfTokenField;
    }

    /**
     * @return string
     */
    public function getCsrfTokenValue()
    {
        return $this->csrfTokenField->getAttribute('value');
    }
}
