<?php

namespace Olcs\XmlTools\Xml\Specification;

/**
 * Interface SpecificationInterface
 * @package Olcs\XmlTools\Xml\Specification
 */
interface SpecificationInterface
{
    /**
     * @return mixed
     */
    public function apply(\DOMElement $domElement);
}
