<?php

namespace Nextform\Fields\Validation;

class ValidationModel
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
}