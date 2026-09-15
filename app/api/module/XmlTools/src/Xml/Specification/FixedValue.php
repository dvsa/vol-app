<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class FixedValue
 * @package Olcs\XmlTools\Xml\Specification
 */
class FixedValue extends AbstractCapturingNode
{
    protected $value;

    /**
     * @param $destination
     * @param $value
     */
    public function __construct($destination, $value)
    {
        $this->destination = $destination;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function apply(\DOMElement $domElement)
    {
        return $this->createReturnValue($this->getValue());
    }
}
