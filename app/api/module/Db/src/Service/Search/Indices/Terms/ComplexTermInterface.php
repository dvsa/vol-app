<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Db\Service\Search\Indices\Terms;

interface ComplexTermInterface
{
    public function applySearch(array &$params): void;
}
