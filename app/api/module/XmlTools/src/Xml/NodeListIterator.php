<?php

namespace Olcs\XmlTools\Xml;

use DOMNode;

/**
 * @template-implements \Iterator<int, \DOMNode>
 */
class NodeListIterator implements \Iterator
{
    /**
     * @var \DOMNodeList
     */
    protected $target;

    /**
     * @var integer
     */
    protected $key = 0;

    public function __construct(\DOMNodeList $domNodeList)
    {
        $this->target = $domNodeList;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return DOMNode|null Can return any type.
     */
    #[\Override]
    public function current(): ?DOMNode
    {
        return $this->target->item($this->key());
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    #[\Override]
    public function next(): void
    {
        ++$this->key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int|null scalar on success, or null on failure.
     */
    #[\Override]
    public function key(): ?int
    {
        return $this->key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    #[\Override]
    public function valid(): bool
    {
        return $this->target->length > $this->key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    #[\Override]
    public function rewind(): void
    {
        $this->key = 0;
    }
}
