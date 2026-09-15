<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Class MultiRecursionValue
 * @package Olcs\XmlTools\Xml\Specification
 */
class MultiRecursionValue extends AbstractCapturingNode
{
    protected $recursion;

    /**
     * @param $destination
     */
    public function __construct($destination, SpecificationInterface $specification)
    {
        $this->destination = $destination;
        $this->recursion = $specification;
    }

    /**
     * @return SpecificationInterface
     */
    public function getRecursion()
    {
        return $this->recursion;
    }

    /**
     * @return array
     */
    #[\Override]
    public function apply(\DOMElement $domElement)
    {
        return $this->createReturnValue([$this->getRecursion()->apply($domElement)]);
    }
}
