<?php

namespace Permits\Form;

use Common\Form\Elements\Types\Html;
use ReflectionClass;
use Zend\Form as ZendForm;

/**
 * Form
 */
class Form extends ZendForm\Form
{
    /**
     * Form constructor. Prevents browser HTML5 form validations
     *
     * @param null $name Form Name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('novalidate', 'novalidate');
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * Clone
     *
     * @return void
     */
    public function __clone()
    {
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties();

        foreach ($props as $prop) {
            $name = $prop->getName();

            $value = $this->$name;
            if (is_object($value)) {
                $this->$name = clone $this->$name;
            }
        }
    }

    /**
     * Prevent a form from being validated (and thus saved) if it is set read only
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->getOption('readonly')) {
            return false;
        }

        return parent::isValid();
    }

    public function populateValues($data, $onlyBase = false)
    {
        $populateDepth = &self::getPopulateDepth();
        try {
            $populateDepth += 1;
            parent::populateValues($data, $onlyBase);
        } finally {
            $populateDepth -= 1;
        }
    }

    /**
     * A design flaw in @see Html means that JS may be injected into a rendered page by submitting it to such a 'field'
     * This will function allows the Html to know that a value being set is coming from user data so it can prevent the
     * injection.
     *
     * @return int
     */
    private static function &getPopulateDepth()
    {
        static $populateDepth = 0;
        return $populateDepth;
    }

    public static function isPopulating()
    {
        return self::getPopulateDepth() !== 0;
    }
}
