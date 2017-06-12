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
	 * @return boolean
	 */
	private function read() {
		$defaultErrors = [];
		$rootField = $this->fieldFactory->createField($this->xmlElement->getName());

		// Read form field (root)
		foreach ($this->xmlElement->attributes() as $name => $value) {
			$rootField->setAttribute($name, (string) $value);
		}

		$this->fields[] = $rootField;

		// Read defaults
		if (property_exists($this->xmlElement, static::DEFAULTS_KEY)) {
			$defaults = $this->xmlElement->{static::DEFAULTS_KEY};

			foreach ($defaults->children() as $name => $child) {
				if ($name == static::VALIDATION_KEY) {
					if (array_key_exists(static::VALIDATION_ERRORS_KEY, $child)) {
						$errors = $child->{static::VALIDATION_ERRORS_KEY};

						foreach ($errors->children() as $name => $error) {
							$defaultErrors[$name] = (string) $error;
						}
					}
				}
			}
		}

		// Read all fields
		foreach ($this->xmlElement as $name => $element) {
			if ($this->fieldFactory->hasField($name)) {
				$field = $this->fieldFactory->createField($name);

				foreach ($element->attributes() as $name => $value) {
					$field->setAttribute($name, (string) $value);
				}

				if (property_exists($element, static::VALIDATION_KEY)) {
					$validationElement = $element->{static::VALIDATION_KEY};
					$errors = [];

					if (property_exists($validationElement, static::VALIDATION_ERRORS_KEY)) {
						$errorElement = $validationElement->{static::VALIDATION_ERRORS_KEY};

						foreach ($errorElement->children() as $name => $error) {
							$errors[$name] = (string) $error;
						}
					}

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

				}

				$this->fields[] = $field;
			}
		}

		return true;
	}
}