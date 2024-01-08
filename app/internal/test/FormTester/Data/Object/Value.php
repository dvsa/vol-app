<?php

namespace Dvsa\OlcsTest\FormTester\Data\Object;

/**
 * Class Value
 * @package Olcs\TestHelpers\FormTester\Data\Object
 */
class Value
{
    const VALID = true;
    const INVALID = false;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var boolean
     */
    protected $valid;

    /**
     * @internal boolean $valid
     * @internal mixed $value
     * @internal Context $context...
     */
    public function __construct()
    {
        $args = func_get_args();

        $this->valid = array_shift($args);
        $this->value = array_shift($args);
        $this->context = $args;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
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
    public function getContextArray()
    {
        $data = [];
        foreach ($this->getContext() as $ctx) {
            /** @var Context $data */
            $data = array_merge_recursive($data, $ctx->getData());
        }

        return $data;
    }

    /**
     * @param Stack $stack
     * @return array
     */
    public function getData(Stack $stack)
    {
        $value = $stack->getTransposed($this->getValue());
        $data = $this->getContextArray();
        $data = array_merge_recursive($data, $value);

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode(['valid' => $this->valid, 'value' => $this->value, 'context' => $this->context]);
    }
}
