<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class MultiNodeValue
 * @package Olcs\XmlTools\Xml\Specification
 */
class MultiNodeValue extends AbstractCapturingNode
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
        return $this->createReturnValue([$domElement->nodeValue]);
    }
}
