<?php

namespace Nextform\Config;

use Nextform\Fields\InputField;
use Nextform\Security\Csrf\TokenManager as CsrfTokenManager;

trait CsrfTokenAdapter
{
    /**
     * @var string
     */
    private $csrfTokenFieldName = 'nextform_csrf_token_';

    /**
     * @var string
     */
    private $csrfTokenHeaderNames = ['X-CSRF-Token', 'X-Csrf-Token'];

    /**
     * @var string
     */
    private $csrfTokenHeaderSeparator = ':';

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
     * @return Nextform\Security\Csrf\Model\TokenModel
     */
    public function enableCsrfToken($enabled = true)
    {
        $this->csrfTokenEnabled = $enabled;

        if (true == $this->csrfTokenEnabled) {
            $this->csrfTokenManager = new CsrfTokenManager();
            $this->csrfTokenField = new InputField();

            $token = $this->csrfTokenManager->getToken($this->getCsrfTokenFieldNameUid());

            $this->csrfTokenField->setGhost(true);
            $this->csrfTokenField->setAttribute('hidden', '1');
            $this->csrfTokenField->setAttribute('name', $this->getCsrfTokenFieldName());
            $this->csrfTokenField->setAttribute('value', $token->value);

            $this->addField($this->csrfTokenField);

            return $token;
        } elseif ($this->csrfTokenField instanceof InputField) {
            $this->csrfTokenManager->deleteToken($this->getCsrfTokenFieldNameUid());
            $this->csrfTokenManager = null;

            $this->removeField($this->csrfTokenField);
            $this->csrfTokenField = null;
        }

        return null;
    }

    /**
     * @param array $input
     * @return boolean
     */
    public function checkCsrfToken($input = [])
    {
        if ( ! $this->csrfTokenEnabled) {
            return true;
        }

        $headers = getallheaders();
        $tokenManager = $this->csrfTokenManager;
        $fieldName = $this->getCsrfTokenFieldName();
        $useHeader = false;
        $headerName = '';
        $inputToken = null;

        foreach ($this->csrfTokenHeaderNames as $name) {
            if (array_key_exists($name, $headers)) {
                $useHeader = true;
                $headerName = $name;
                break;
            }
        }

        if (true == $useHeader) {
            list($tokenId, $tokenValue) = explode(
                $this->csrfTokenHeaderSeparator,
                $headers[$headerName]
            );

            $inputToken = $tokenManager->createToken($tokenId, $tokenValue);
        } elseif (array_key_exists($fieldName, $input)) {
            $tokenId = $this->getCsrfTokenFieldNameUid();
            $fieldName = $this->getCsrfTokenFieldName();
            $inputToken = $tokenManager->createToken($tokenId, $input[$fieldName]);
        }

        return $inputToken ? $tokenManager->isValidToken($inputToken) : false;
    }

    /**
     * @return boolean
     */
    public function isCsrfTokenEnabled()
    {
        return $this->csrfTokenEnabled;
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
        return $this->csrfTokenFieldName . $this->getCsrfTokenFieldNameUid();
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
