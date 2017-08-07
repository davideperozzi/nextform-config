<?php

namespace Nextform\Fields;

abstract class AbstractField
{
    const UID_SEPERATOR = '_';

    /**
     * @var string
     */
    const UID_PREFIX = 'field' . self::UID_SEPERATOR;

    /**
     * @var string
     */
    const UID_ATTRIBUTE = 'name';

    /**
     * @var boolean
     */
    public static $root = false;

    /**
     * @var boolean
     */
    public static $wrapper = false;

    /**
     * @var boolean
     */
    public static $yield = [];

    /**
     * @var string
     */
    public static $tag = '';

    /**
     * @var integer
     */
    protected static $counter = 0;

    /**
     * @var string
     */
    public $id = '';

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
     * @var array
     */
    protected $changeCallbacks = [];


    public function __construct()
    {
        static::$counter++;

        $this->id = static::generateUid();
    }

    public function ready()
    {
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function onChange(callable $callback)
    {
        $this->changeCallbacks[] = $callback;

        return $this;
    }

    private function triggerChange()
    {
        foreach ($this->changeCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * @return string
     */
    private static function generateUid()
    {
        return self::UID_PREFIX . static::$counter;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        if ($name == self::UID_ATTRIBUTE) {
            $this->id = $value;
        }

        $this->triggerChange();
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function removeAttribute($name)
    {
        $found = array_key_exists($name, $this->attributes);

        if (true == $found) {
            unset($this->attributes[$name]);
            $this->triggerChange();
        }

        return $found;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $error
     * @return Validation\ValidationModel
     */
    public function addValidation($name, $value, $error = '')
    {
        $model = new Validation\ValidationModel($name, $value);

        if ( ! empty($error)) {
            $model->error = new Validation\ErrorModel(sprintf($error, $value));
        }

        $this->validation[] = $model;

        $this->triggerChange();

        return $model;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $action
     * @param string $error
     * @return Validation\ValidationModel
     */
    public function addConnectedValidation($name, $value, $action = '', $error = '')
    {
        $model = $this->addValidation($name, $value, $error);
        $model->connection = new Validation\ConnectionModel($action);

        $this->triggerChange();

        return $model;
    }

    /**
     * @param AbstractField $field
     */
    public function addChild(&$field)
    {
        $this->children[] = $field;

        $this->triggerChange();
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;

        $this->triggerChange();
    }

    /**
     * @param string $name
     * @return array|Validation\ErrorModel
     */
    public function getValidation($name = '')
    {
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
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return number
     */
    public function countChildren()
    {
        return count($this->children);
    }

    /**
     * @return string
     */
    public function getContnet()
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param string $name
     * @throws Exception\AttributeNotFoundException if attribute not found
     * @return string
     */
    public function getAttribute($name)
    {
        if ( ! $this->hasAttribute($name)) {
            throw new Exception\AttributeNotFoundException(
                sprintf('Attribute "%s" not found', $name)
            );
        }

        return $this->attributes[$name];
    }
}
