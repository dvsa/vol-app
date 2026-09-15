<?php

namespace Olcs\XmlTools\Xml;

/**
 * @template-extends \FilterIterator<int, \DOMNode, \Iterator<int, \DOMNode>>
 */
class ElementIterator extends \FilterIterator
{
    /**
     * @template-extends \Iterator<int, \DOMElement>
     */
    #[\Override]
    public function accept(): bool
    {
        return $this->getInnerIterator()->current() instanceof \DOMElement;
    }
}
