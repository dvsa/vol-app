<?php

namespace Olcs\XmlTools\Xml;

use Iterator;

/**
 * @template-extends \FilterIterator<int, \DOMNode, \Iterator<int, \DOMNode>>
 */
class TagNameFilterIterator extends \FilterIterator
{
    /**
     * @var array
     */
    protected $tags;

    /**
     * @param array $tags
     */
    public function __construct(Iterator $iterator, $tags = [])
    {
        $this->tags = array_combine($tags, $tags);
        parent::__construct($iterator);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Check whether the current element of the iterator is acceptable
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     */
    #[\Override]
    public function accept(): bool
    {
        /** @var \DOMElement $domElement */
        $domElement = $this->getInnerIterator()->current();
        return isset($this->tags[$domElement->tagName]);
    }
}
