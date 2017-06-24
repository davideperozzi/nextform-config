<?php

namespace Nextform\Parser\Reader;

use Nextform\Parser\FieldFactory;

abstract class AbstractReader
{
	/**
	 * @var string
	 */
	const VALIDATION_KEY = 'validation';

	/**
	 * @var string
	 */
	const VALIDATION_MODIFIERS_KEY = 'modifiers';

	/**
	 * @var string
	 */
	const VALIDATION_ERRORS_KEY = 'errors';

	/**
	 * @var string
	 */
	const VALIDATION_CONNECTIONS_KEY = 'connections';

	/**
	 * @var string
	 */
	const VALIDATION_CONNECTIONS_ACTIONS_KEY = 'actions';

	/**
	 * @var string
	 */
	const VALIDATION_CONNECTIONS_ACTION_SEPERATOR = ':';

	/**
	 * @var string
	 */
	const VALIDATION_MODIFIER_SEPERATOR = '-';

	/**
	 * @var string
	 */
	const COLLECTION_KEY = 'collection';

	/**
	 * @var string
	 */
	const DEFAULTS_KEY = 'defaults';

	/**
	 * @param FieldFactory $fieldFactory
	 */
	protected $fieldFactory;

	/**
	 * @var array
	 */
	protected $fields = [];

	/**
	 * @param FieldFactory $fieldFactory
	 */
	public function __construct(FieldFactory &$fieldFactory) {
		$this->fieldFactory = $fieldFactory;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param string $file
	 * @return boolean
	 */
	abstract public function load($file);
}