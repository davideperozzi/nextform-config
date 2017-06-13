<?php

namespace Nextform\Fields;

abstract class AbstractField
{
	/**
	 * @var boolean
	 */
	public static $root = false;

	/**
	 * @var boolean
	 */
	public static $yield = [];

	/**
	 * @var string
	 */
	public static $tag = '';

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @var array
	 */
	protected $children = [];

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var array
	 */
	protected $validation = [];

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function addValidation($name, $value, $error = '') {
		$model = new Validation\ValidationModel($name, $value);

		if (!empty($error)) {
			$model->error = new Validation\ErrorModel(sprintf($error, $value));
		}

		$this->validation[] = $model;
	}

	/**
	 * @param AbstractField $field
	 */
	public function addChild(&$field) {
		$this->children[] = $field;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @param string $name
	 * @return array|Validation\ErrorModel
	 */
	public function getValidation($name = '') {
		if ( ! empty($name)) {
			foreach ($this->validation as $validation) {
				if ($validation->name == $name) {
					return $validation;
				}
			}

			return null;
		}

		return $this->validation;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @return array
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return string
	 */
	public function getContnet() {
		return $this->content;
	}

	/**
	 * @return array
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return count($this->children) > 0;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasAttribute($name) {
		return array_key_exists($name, $this->attributes);
	}

	/**
	 * @param string $name
	 * @return string
	 * @throws Exception\AttributeNotFoundException if attribute not found
	 */
	public function getAttribute($name) {
		if ( ! $this->hasAttribute($name))	{
			throw new Exception\AttributeNotFoundException(
				sprintf('Attribute "%s" not found', $name)
			);
		}

		return $this->attributes[$name];
	}
}