<?php

namespace Nextform\Fields\Validation;

class ConnectionModel
{
	/**
	 * @var string
	 */
	const ACTION_CONTENT = 'content';

	/**
	 * @var string
	 */
	const ACTION_VALIDATE = 'validate';

	/**
	 * @var string
	 */
	public $action = self::ACTION_VALIDATE;

	/**
	 * @param string $action
	 */
	public function __construct($action = '') {
		if ( ! empty($action)) {
			$this->action = $action;
		}
	}
}