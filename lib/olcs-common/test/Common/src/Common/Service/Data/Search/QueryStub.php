<?php

namespace CommonTest\Common\Service\Data\Search;

use ArrayObject;

/**
 * Class QueryStub
 * @template-extends ArrayObject<array-key, mixed>
 */
class QueryStub extends ArrayObject
{
    public $limit;
    public $page;

    public function __construct(
        $limit = null,
        $page = null,
        $input = [],
        $flags = 0,
        $iterator_class = "ArrayIterator"
    ) {
        $this->limit = $limit;
        $this->page = $page;
        parent::__construct($input, $flags, $iterator_class);
    }
}
