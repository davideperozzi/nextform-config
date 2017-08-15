<?php

namespace Nextform\Fields;

abstract class AbstractField
{
    /**
     * @var string
     */
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
     * @var string
     */
    const SIGNATURE_SEPERATOR = '_';

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var integer
     */
    protected static $counter = 0;

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
     * @var boolean
     */
    protected $ghost = false;

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

    /**
     * @return string
     */
    public function getSignature()
    {
        if ($this->ghost) {
            return '';
        }

        $class = get_class($this);
        $uid = $this->getAttribute(self::UID_ATTRIBUTE, '');
        $str = $this->generateSignature($uid, $class::$tag);

        foreach ($this->children as $child) {
            $class = get_class($child);
            $uid = $child->getAttribute(self::UID_ATTRIBUTE, '');
            $str .= $this->generateSignature($uid, $class::$tag);
        }

        return $str;
    }

    /**
     * @param string $name
     * @param string $tag
     * @return string
     */
    private function generateSignature($name, $tag)
    {
        return $tag . self::SIGNATURE_SEPERATOR . $name;
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
     * @return self
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        if ($name == self::UID_ATTRIBUTE) {
            $this->id = $value;
        }

        $this->triggerChange();

        return $this;
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
     * @return self
     */
    public function addChild(&$field)
    {
        $this->children[] = $field;

        $this->triggerChange();

        return $this;
    }

    /**
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        $this->triggerChange();

        return $this;
    }

    /**
     * @param boolean $enabled
     * @return self
     */
    public function setGhost($enabled)
    {
        $this->ghost = $enabled;

        return $this;
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
     * @return boolean
     */
    public function isGhost()
    {
        return $this->ghost;
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
     * @param string|null $fallbackVal
     * @throws Exception\AttributeNotFoundException if attribute not found
     * @return string
     */
    public function getAttribute($name, $fallbackVal = null)
    {
        if ( ! $this->hasAttribute($name)) {
            if ( ! is_null($fallbackVal)) {
                return $fallbackVal;
            }

            throw new Exception\AttributeNotFoundException(
                    sprintf('Attribute "%s" not found', $name)
                );
        }

        return $this->attributes[$name];
    }
}
