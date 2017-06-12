<?php

namespace Nextform\Fields\Validation;

class ErrorModel
{
	/**
	 * @var string
	 */
	public $message = '';

	/**
	 * @param string $message
	 */
	public function __construct($message) {
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->message;
	}
}