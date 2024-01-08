<?php

namespace Dvsa\OlcsTest\FormTester\Data\Object;

/**
 * Class Context
 * @package Olcs\TestHelpers\FormTester\Data\Object
 */
class Context
{
    /**
     * @var Stack
     */
    protected $stack;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param Stack $stack
     * @param mixed $value
     */
    public function __construct(Stack $stack, $value)
    {
        $this->stack = $stack;
        $this->value = $value;
    }

    /**
     * @return Stack
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->getStack()->getTransposed($this->getValue());
    }
}
