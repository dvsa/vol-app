<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class NodeAttribute
 * @package Olcs\XmlTools\Xml\Specification
 */
class NodeAttribute extends AbstractCapturingNode
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @param $destination
     * @param $property
     */
    public function __construct($destination, $property)
    {
        $this->destination = $destination;
        $this->property = $property;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return array
     */
    #[\Override]
    public function apply(\DOMElement $domElement)
    {
        return $this->createReturnValue($domElement->getAttribute($this->getProperty()));
    }
}
