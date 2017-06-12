<?php

namespace Nextform\Fields;

class FieldCollection implements Traversable
{
	/**
	 * @var AbstractField
	 */
	private $root = null;

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
	 * @throws Exception\RootFieldNotFound if no root was defined
	 */
	public function __construct(array $fields) {
		$this->fields = $fields;

		foreach ($this->fields as $field) {
			if (true == $field::$root) {
				if (is_null($this->root)) {
					$this->root = $field;
				}
				else {
					throw new Exception\RootFieldDuplicateException(
						'Found multiple root elements for one config.'
					);
				}
			}

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

		// Handle root as seperated field
		if ($this->root) {
			array_splice($this->fields, array_search($this->root, $this->fields), 1);
		}
		else {
			throw new Exception\RootFieldNotFoundException('No root field was found');
		}
	}

	/**
	 * @return AbstractField
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * @return integer
	 */
	public function count() {
		return count($this->fields);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator() {
		return new \ArrayIterator($this->fields);
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