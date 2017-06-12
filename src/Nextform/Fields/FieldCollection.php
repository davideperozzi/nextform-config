<?php

namespace Nextform\Fields;

class FieldCollection
{
	/**
	 * @var array
	 */
	private $fields = [];

	/**
	 * @var array
	 */
	private $nameMap = [];

	/**
	 * @param array $fields
	 * @throws Exception\DuplicateFieldException if duplicate field found
	 */
	public function __construct(array $fields) {
		$this->fields = $fields;

		foreach ($this->fields as $field) {
			if ($field->hasAttribute('name')) {
				$name = $field->getAttribute('name');

				if (array_key_exists($name, $this->nameMap)) {
					throw new Exception\DuplicateFieldException(
						sprintf('Field "%s" already defined', $name)
					);
				}

				$this->nameMap[$name] = $field;
			}
		}
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function has($name) {
		return array_key_exists($name, $this->nameMap);
	}

	/**
	 * @param string $name
	 * @return AbstractField
	 */
	public function get($name) {
		if ($this->has($name)) {
			return $this->nameMap[$name];
		}

		return null;
	}
}