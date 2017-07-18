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
	 *
	 */
	public function ready() {
		// if ( ! $this->hasAttribute('array')) {
		// 	throw new Exception\AttributeNotFoundException(
		// 		'The collection field needs a array destination for the subfields'
		// 	);
		// }
		// else if (empty(trim($this->getAttribute('array')))) {
		// 	throw new Exception\InvalidArrayDestinationException(
		// 		'The collection field needs a valid array destination'
		// 	);
		// }
	}

	/**
	 * @param AbstractField &$child
	 */
	public function addChild(&$child) {
		// $count = count(array_map(
		// 	function($field){
		// 		return $field->id;
		// 	},
		// $this->children));

		// // Save plain id for resetting it after changing the name
		// $oldId = $child->id;

		// // Append array to name attribute
		// if ($child->hasAttribute('name')) {
		// 	$name = $child->getAttribute('name');
		// 	$array = $this->getAttribute('array');

		// 	$child->setAttribute('name', $name . $array);
		// }

		// // Update child id to make it unique again
		// $child->id = $oldId . self::UID_SEPERATOR . $count;

		if ( ! $this->hasAttribute('name')) {
			throw new Exception\AttributeNotFoundException('Every collection needs a name');
		}

		$collectionName = $this->getAttribute('name');

		if ( ! $child->hasAttribute('name')) {
			throw new Exception\AttributeNotFoundException('Every collection child needs a name');
		}
		else {
			$name = explode('[', $child->getAttribute('name'));

			if ($name[0] != $collectionName) {
				throw new Exception\InvalidCollectionChildName(
					sprintf(
						'Every child name of a collection needs to start with the name of the corresponding collection. This is required to match a certain validation input to the validator. Found field "%s" in collection "%s"',
						$name[0],
						$collectionName
					)
				);
			}
		}

		parent::addChild($child);
	}

	/**
	 * @return array
	 */
	// public function getArrayStructure() {
		// preg_match_all('/\[(.*?)\]/', $this->getAttribute('array'), $matches);

		// $keys = $matches[1];
		// $arr = [];

		// for($i = count($keys) - 1; $i >= 0; $i--) {
		// 	if (empty(trim($keys[$i]))) {
		// 		$keys[$i] = 0;
		// 	}

		//     $arr = [$keys[$i] => $arr];
		// }

		// return $arr;
	// }
}