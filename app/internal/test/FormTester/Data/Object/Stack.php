<?php

namespace Dvsa\OlcsTest\FormTester\Data\Object;

/**
 * Class Stack
 * @package Olcs\TestHelpers\FormTester\Data\Object
 */
class Stack
{
    /**
     * @var array
     */
    protected $stack;

    /**
     * @param $stack
     */
    public function __construct($stack)
    {
        $this->stack = array_reverse($stack);
    }

    /**
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * @param null $value
     * @return array
     */
    public function getTransposed($value = null)
    {
        $stack = $this->getStack();

        if (is_null($value)) {
            $value = [array_shift($stack)];
        }

        foreach ($stack as $fieldName) {
            $tmp = $value;
            $value = [];
            $value[$fieldName] = $tmp;
        }
        return $value;
    }
}
