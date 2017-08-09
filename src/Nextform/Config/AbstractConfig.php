<?php

namespace Nextform\Config;

use Nextform\Fields\AbstractField;

abstract class AbstractConfig
{
    /**
     * @return array
     */
    abstract public function getFields();

    /**
     * @param AbstractField &$field
     */
    abstract public function addField(AbstractField &$field);

    /**
     * @param AbstractField &$field
     * @return boolean
     */
    abstract public function removeField(AbstractField &$field);
}
