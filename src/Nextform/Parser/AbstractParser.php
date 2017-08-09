<?php

namespace Nextform\Parser;

use Nextform\Parser\Exception\LogicException;
use Nextform\Parser\Reader\AbstractReader;

abstract class AbstractParser
{
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldCollection
     */
    protected $fieldCollection;

    /**
     * @var array
     */
    protected $additionalFields = [];

    /**
     * @var AbstractReader
     */
    protected $reader;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->fieldFactory = new FieldFactory();
    }

    /**
     * @throws LogicException if nor reader was defined
     * @return array
     */
    public function getFields()
    {
        if ( ! $this->reader) {
            throw new LogicException('No reader configured');
        }

        if (is_null($this->fieldCollection)) {
            $this->fieldCollection = new FieldCollection(
                $this->reader->getFields()
            );
        }

        return $this->fieldCollection->with($this->additionalFields);
    }

    /**
     * @param AbstractField $field
     */
    public function addField($field)
    {
        $this->additionalFields[] = $field;
    }

    /**
     * @param AbstractField $field
     * @return boolean
     */
    public function removeField($field)
    {
        return array_splice(
            $this->additionalFields,
            array_search(
                $field,
                $this->additionalFields
            ),
            1
        );
    }
}
