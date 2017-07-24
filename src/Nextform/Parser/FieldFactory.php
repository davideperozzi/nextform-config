<?php

namespace Nextform\Parser;

use Nextform\Fields\AbstractField;

class FieldFactory
{
    /**
     * @var string[]
     */
    private $fieldCtors = [
        'Nextform\Fields\InputField',
        'Nextform\Fields\SelectField',
        'Nextform\Fields\TextareaField',
        'Nextform\Fields\OptionField',
        'Nextform\Fields\FormField',
        'Nextform\Fields\CollectionField'
    ];

    /**
     * @var string[]
     */
    private $ctorCache = [];


    public function __construct()
    {
    }

    /**
     * @param string $tag
     * @return AbstractField
     */
    public function createField($tag)
    {
        if (array_key_exists($tag, $this->ctorCache)) {
            return new $this->ctorCache[$tag];
        }

        foreach ($this->fieldCtors as $ctor) {
            if ($ctor::$tag == $tag) {
                $this->ctorCache[$tag] = $ctor;
                return new $ctor();
            }
        }

        return null;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public function hasField($tag)
    {
        if (array_key_exists($tag, $this->ctorCache)) {
            return true;
        }

        foreach ($this->fieldCtors as $ctor) {
            if ($ctor::$tag == $tag) {
                $this->ctorCache[$tag] = $ctor;
                return true;
            }
        }

        return false;
    }
}
