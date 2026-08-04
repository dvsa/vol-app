<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class NodeValue
 * @package Olcs\XmlTools\Xml\Specification
 */
class NodeValue extends AbstractCapturingNode
{
    /**
     * @param $destination
     */
    public function __construct($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return array
     */
    #[\Override]
    public function apply(\DOMElement $domElement)
    {
        return $this->createReturnValue($domElement->nodeValue);
    }
}
