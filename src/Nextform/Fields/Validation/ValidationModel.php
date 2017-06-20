<?php

namespace Nextform\Fields\Validation;

class ValidationModel implements \JsonSerializable
{
	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $value = '';

	/**
	 * @var ErrorModel
	 */
	public $error = null;

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value = '') {
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'name' => $this->name,
			'value' => $this->value,
			'error' => (string) $this->error
		];
	}
}