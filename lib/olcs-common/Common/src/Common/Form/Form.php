<?php

namespace Common\Form;

use Common\Form\Elements\Types\Html;
use ReflectionClass;
use Laminas\Form as LaminasForm;

/**
 * @template TFilteredValues
 * @extends LaminasForm\Form<TFilteredValues>
 */
class Form extends LaminasForm\Form implements \Stringable
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
    #[\Override]
    public function __toString(): string
    {
        return static::class;
    }

    /**
     * Clone
     *
     * @return void
     */
    #[\Override]
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
     */
    #[\Override]
    public function isValid(): bool
    {
        if ($this->getOption('readonly')) {
            return false;
        }

        return parent::isValid();
    }

    /**
     * @param string[] $data
     *
     * @psalm-param array{html?: '<script>alert("TEST")</script>', text?: '<script>alert("TEST")</script>'} $data
     */
    #[\Override]
    public function populateValues($data, $onlyBase = false): void
    {
        $populateDepth = &self::getPopulateDepth();
        try {
            ++$populateDepth;
            parent::populateValues($data, $onlyBase);
        } finally {
            --$populateDepth;
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

    public static function isPopulating(): bool
    {
        return self::getPopulateDepth() !== 0;
    }
}
