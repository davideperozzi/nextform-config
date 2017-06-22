<?php

namespace Nextform\Fields;

class CollectionField extends AbstractField
{
	/**
	 * @var string
	 */
	public static $tag = 'collection';

	/**
	 * @var boolean
	 */
	public static $wrapper = true;

	/**
	 * @param AbstractField &$child
	 */
	public function addChild(&$child) {
		$count = count(array_map(
			function($field){
				return $field->id;
			},
		$this->children));

		// Save plain id for resetting it after changing the name
		$oldId = $child->id;

		// Append array to name attribute
		if ($this->hasAttribute('array')) {
			if ($child->hasAttribute('name')) {
				$name = $child->getAttribute('name');
				$array = $this->getAttribute('array');

				$child->setAttribute('name', $name . $array);
			}
		}

		// Update child id to make it unique again
		$child->id = $oldId . self::UID_SEPERATOR . $count;

		parent::addChild($child);
	}

	/**
	 * @return array
	 */
	public function getArrayStructure() {
		preg_match_all('/\[(.*?)\]/', $this->getAttribute('array'), $matches);

		$keys = $matches[1];
		$arr = [];

		for($i = count($keys) - 1; $i >= 0; $i--) {
			if (empty(trim($keys[$i]))) {
				$keys[$i] = 0;
			}

		    $arr = [$keys[$i] => $arr];
		}

		return $arr;
	}
}