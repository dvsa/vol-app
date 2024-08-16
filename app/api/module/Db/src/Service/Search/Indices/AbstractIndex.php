<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Db\Service\Search\Indices;

use InvalidArgumentException;
use Dvsa\Olcs\Db\Service\Search\Indices\Terms\ComplexTermInterface;
use Dvsa\Olcs\Db\Service\Search\Indices\Terms\TransportManagerLicenceStatus;

abstract class AbstractIndex
{
    protected array $filters;

    /** @return ComplexTermInterface[] */
    abstract public function getFilters(): array;
}
