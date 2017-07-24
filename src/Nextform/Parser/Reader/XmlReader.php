<?php

namespace Nextform\Parser\Reader;

use Nextform\Fields\AbstractField;
use Nextform\Parser\Exception\InvalidConfigException;

class XmlReader extends AbstractReader
{
    /**
     * @var SimpleXMLElement
     */
    private $xmlElement = null;

    /**
     * @var array
     */
    private $defaultErrors = [];

    /**
     * @var array
     */
    private $defaultConnectionErrors =  [];

    /**
     * @param string $content
     * @return boolean
     */
    public function load($content)
    {
        $useInternalErrors = libxml_use_internal_errors(true);
        $this->xmlElement = simplexml_load_string(trim($content));

        if ($this->xmlElement == false) {
            $lastError = libxml_get_last_error();

            if ($lastError) {
                throw new InvalidConfigException(
                    sprintf(
                        'Could not read xml due this error: "%s" on line %s:%s)',
                        trim($lastError->message),
                        $lastError->line,
                        $lastError->column
                    )
                );
            }
            throw new InvalidConfigException('The parsed config is invalid');
        }

        // Reset error handling
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        return $this->read();
    }

    /**
     * @param AbstractField $field
     * @param \SimpleXMLElement $element
     */
    private function yieldElements(&$field, &$element)
    {
        foreach ($field::$yield as $base => $ctor) {
            if (property_exists($element, $base)) {
                foreach ($element->{$base}->children() as $child) {
                    $this->readElement($child, $field);
                }
            }
        }
    }

    /**
     * @param \SimpleXMLElement $element
     * @param AbstractField $parent
     * @return AbstractField
     */
    private function readElement(&$element, &$parent = null)
    {
        $name = $element->getName();

        if ($this->fieldFactory->hasField($name)) {
            $field = $this->fieldFactory->createField($name);

            foreach ($element->attributes() as $name => $value) {
                $field->setAttribute($name, (string) $value);
            }

            if ( ! empty(trim((string) $element))) {
                $field->setContent((string) $element);
            }

            if ( ! empty($field::$yield)) {
                $this->yieldElements($field, $element);
            }

            if (true == $field::$wrapper) {
                foreach ($element->children() as $child) {
                    $this->readElement($child, $field);
                }
            }

            $this->parseValidation($field, $element, $this->defaultErrors);

            if ( ! is_null($parent)) {
                $parent->addChild($field);
            } else {
                $this->fields[] = $field;
            }

            $field->ready();

            return $field;
        }

        return null;
    }

    /**
     * @param AbstractField &$field
     * @param \SimpleXMLElement &$element
     * @param array $errors
     */
    private function parseValidation(&$field, &$element)
    {
        if (property_exists($element, static::VALIDATION_KEY)) {
            $validationElement = $element->{static::VALIDATION_KEY};
            $modifiersElement = property_exists($validationElement, static::VALIDATION_MODIFIERS_KEY)
                                ? $validationElement->{static::VALIDATION_MODIFIERS_KEY}
                                : null;
            $errorElement = property_exists($validationElement, static::VALIDATION_ERRORS_KEY)
                                ? $validationElement->{static::VALIDATION_ERRORS_KEY}
                                : null;

            foreach ($validationElement->attributes() as $name => $value) {
                $error = '';

                // Search error
                if ($errorElement && property_exists($errorElement, $name)) {
                    $error = (string) $errorElement->{$name};
                } elseif (array_key_exists($name, $this->defaultErrors)) {
                    $error = $this->defaultErrors[$name];
                }

                $validation = $field->addValidation($name, (string) $value, $error);

                // Search modifiers
                if ($modifiersElement) {
                    foreach ($modifiersElement->attributes() as $modifier => $modifierVal) {
                        $startName = $validation->name . static::VALIDATION_MODIFIER_SEPERATOR;

                        if (substr($modifier, 0, strlen($startName)) == $startName) {
                            $modifierName = substr($modifier, strlen($startName) - strlen($modifier));

                            $validation->addModifier($modifierName, (string) $modifierVal);
                        }
                    }
                }
            }

            // Read connections
            if (property_exists($validationElement, static::VALIDATION_CONNECTIONS_KEY)) {
                $connectionsElement = $validationElement->{static::VALIDATION_CONNECTIONS_KEY};
                $connectionActions = [];
                $connectionErrors = [];

                if ($connectionsElement->count() > 0) {
                    foreach ($connectionsElement->children() as $child) {
                        if ($child->getName() == static::VALIDATION_CONNECTIONS_ACTIONS_KEY) {
                            foreach ($child->attributes() as $name => $value) {
                                $connectionActions[$name] = (string) $value;
                            }
                        }
                    }
                }

                if ($errorElement && property_exists($errorElement, static::VALIDATION_CONNECTIONS_KEY)) {
                    $connectionsErrorElement = $errorElement->{static::VALIDATION_CONNECTIONS_KEY};

                    foreach ($connectionsErrorElement->children() as $name => $error) {
                        if ($error->count() == 0) {
                            $connectionErrors[$name] = (string) $error;
                        }
                    }
                }

                foreach ($connectionsElement->attributes() as $name => $value) {
                    $error = '';
                    $action = '';
                    $value = (string) $value;

                    if (array_key_exists($name, $connectionErrors)) {
                        $error = $connectionErrors[$name];
                    } elseif (array_key_exists($name, $this->defaultConnectionErrors)) {
                        $error = $this->defaultConnectionErrors[$name];
                    }

                    if (array_key_exists($name, $connectionActions)) {
                        $action = $connectionActions[$name];
                    }

                    if (preg_match('/^(.*)' . static::VALIDATION_CONNECTIONS_ACTION_SEPERATOR . '(.*)$/', $value, $matches)) {
                        if (array_key_exists(1, $matches)) {
                            $action = $matches[1];
                        }

                        if (array_key_exists(2, $matches)) {
                            $value = $matches[2];
                        }
                    }

                    $field->addConnectedValidation($name, $value, $action, $error);
                }
            }
        }
    }

    /**
     * @return boolean
     */
    private function read()
    {
        // Read form field (root)
        $this->readElement($this->xmlElement);

        // Read defaults
        if (property_exists($this->xmlElement, static::DEFAULTS_KEY)) {
            $defaults = $this->xmlElement->{static::DEFAULTS_KEY};

            foreach ($defaults->children() as $name => $child) {
                if ($name == static::VALIDATION_KEY) {
                    if (array_key_exists(static::VALIDATION_ERRORS_KEY, $child)) {
                        $errorElement = $child->{static::VALIDATION_ERRORS_KEY};

                        foreach ($errorElement->children() as $name => $error) {
                            if ($error->count() == 0) {
                                $this->defaultErrors[$name] = (string) $error;
                            }
                        }

                        // Default errors for connection validation
                        if (property_exists($errorElement, static::VALIDATION_CONNECTIONS_KEY)) {
                            $connectionsErrorElement = $errorElement->{static::VALIDATION_CONNECTIONS_KEY};

                            foreach ($connectionsErrorElement->children() as $name => $error) {
                                if ($error->count() == 0) {
                                    $this->defaultConnectionErrors[$name] = (string) $error;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Read all fields
        foreach ($this->xmlElement as $name => $element) {
            $field = $this->readElement($element);
        }

        return true;
    }
}
