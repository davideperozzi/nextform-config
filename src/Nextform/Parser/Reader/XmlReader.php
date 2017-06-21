<?php

namespace Nextform\Parser\Reader;

use Nextform\Fields\AbstractField;
use Nextform\Parser\FieldFactory;
use Nextform\Parser\Exception\LogicException;
use Nextform\Parser\Exception\InvalidConfigException;

class XmlReader extends AbstractReader
{
	/**
	 * @var SimpleXMLElement
	 */
	private $xmlElement = null;

	/**
	 * @param string $content
	 * @return boolean
	 */
	public function load($content) {
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
			else {
				throw new InvalidConfigException('The parsed config is invalid');
			}
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
	private function yieldElements(&$field, &$element) {
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
	private function readElement(&$element, &$parent = null) {
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

			if ( ! is_null($parent)) {
				$parent->addChild($field);
			}
			else {
				$this->fields[] = $field;
			}

			return $field;
		}

		return null;
	}

	/**
	 * @return boolean
	 */
	private function read() {
		$defaultErrors = [];
		$defaultConnectionErrors = [];

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
								$defaultErrors[$name] = (string) $error;
							}
						}

						// Default errors for connection validation
						if (property_exists($errorElement, static::VALIDATION_CONNECTIONS_KEY)) {
							$connectionsErrorElement = $errorElement->{static::VALIDATION_CONNECTIONS_KEY};

							foreach ($connectionsErrorElement->children() as $name => $error) {
								if ($error->count() == 0) {
									$defaultConnectionErrors[$name] = (string) $error;
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

			if ( ! is_null($field)) {
				if (property_exists($element, static::VALIDATION_KEY)) {
					$validationElement = $element->{static::VALIDATION_KEY};
					$connectionErrors = [];
					$errors = [];

					// Validation errors
					if (property_exists($validationElement, static::VALIDATION_ERRORS_KEY)) {
						$errorElement = $validationElement->{static::VALIDATION_ERRORS_KEY};

						foreach ($errorElement->children() as $name => $error) {
							if ($error->count() == 0) {
								$errors[$name] = (string) $error;
							}
						}

						// Errors for connection validation
						if (property_exists($errorElement, static::VALIDATION_CONNECTIONS_KEY)) {
							$connectionsErrorElement = $errorElement->{static::VALIDATION_CONNECTIONS_KEY};

							foreach ($connectionsErrorElement->children() as $name => $error) {
								if ($error->count() == 0) {
									$connectionErrors[$name] = (string) $error;
								}
							}
						}
					}

					// Validation options
					foreach ($validationElement->attributes() as $name => $value) {
						$error = '';

						if (array_key_exists($name, $errors)) {
							$error = $errors[$name];
						}
						else if (array_key_exists($name, $defaultErrors)) {
							$error = $defaultErrors[$name];
						}

						$field->addValidation($name, (string) $value, $error);
					}

					// Validation connections
					if (property_exists($validationElement, static::VALIDATION_CONNECTIONS_KEY)) {
						$connectionsElement = $validationElement->{static::VALIDATION_CONNECTIONS_KEY};
						$connectionActions = [];

						if ($connectionsElement->count() > 0) {
							foreach ($connectionsElement->children() as $child) {
								if ($child->getName() == static::VALIDATION_CONNECTIONS_ACTIONS_KEY) {
									foreach ($child->attributes() as $name => $value) {
										$connectionActions[$name] = (string) $value;
									}
								}
							}
						}

						foreach ($connectionsElement->attributes() as $name => $value) {
							$error = '';
							$action = '';

							if (array_key_exists($name, $connectionErrors)) {
								$error = $connectionErrors[$name];
							}
							else if (array_key_exists($name, $defaultConnectionErrors)) {
								$error = $defaultConnectionErrors[$name];
							}

							if (array_key_exists($name, $connectionActions)) {
								$action = $connectionActions[$name];
							}

							$field->addConnectedValidation($name, (string) $value, $action, $error);
						}
					}
				}
			}
		}

		return true;
	}
}