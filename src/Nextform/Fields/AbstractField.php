<?php

namespace Nextform\Fields;

abstract class AbstractField
{
	/**
	 * @var string
	 */
	public static $tag = '';

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