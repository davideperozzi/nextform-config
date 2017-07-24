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


    public function ready()
    {
    }

    /**
     * @param AbstractField &$child
     */
    public function addChild(&$child)
    {
        if ( ! $this->hasAttribute('name')) {
            throw new Exception\AttributeNotFoundException('Every collection needs a name');
        }

        $collectionName = $this->getAttribute('name');

        if ( ! $child->hasAttribute('name')) {
            throw new Exception\AttributeNotFoundException('Every collection child needs a name');
        }
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


        parent::addChild($child);
    }
}
