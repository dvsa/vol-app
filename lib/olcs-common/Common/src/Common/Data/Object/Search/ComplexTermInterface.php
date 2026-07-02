<?php

declare(strict_types=1);

namespace Common\Data\Object\Search;

interface ComplexTermInterface
{
    public function applySearch(array &$params): void;
}
