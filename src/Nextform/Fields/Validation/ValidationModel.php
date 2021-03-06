<?php

namespace Nextform\Fields\Validation;

class ValidationModel implements \JsonSerializable
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $value = '';

    /**
     * @var ErrorModel
     */
    public $error = null;

    /**
     * @var array
     */
    public $modifiers = [];

    /**
     * @var ConnectionModel
     */
    public $connection = null;

    /**
     * @param string $name
     * @param string $value
     * @param ConnectionModel $connection
     */
    public function __construct($name, $value = '', ConnectionModel $connection = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->connection = $connection;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addModifier($name, $value)
    {
        $this->modifiers[$name] = $value;
    }

    /**
     * @return boolean
     */
    public function hasConnection()
    {
        return ! is_null($this->connection);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'error' => (string) $this->error
        ];
    }
}
