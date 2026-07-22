<?php

namespace CommonTest\Common\Service\Data\Search;

use ArrayObject;

/**
 * Class QueryStub
 * @template-extends ArrayObject<array-key, mixed>
 */
class QueryStub extends ArrayObject
{
    public function __construct(
        public $limit = null,
        public $page = null,
        $input = [],
        $flags = 0,
        $iterator_class = "ArrayIterator"
    ) {
        parent::__construct($input, $flags, $iterator_class);
    }
}
