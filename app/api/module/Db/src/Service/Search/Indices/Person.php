<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Db\Service\Search\Indices;

use Dvsa\Olcs\Db\Service\Search\Indices\Terms\ComplexTermInterface;
use Dvsa\Olcs\Db\Service\Search\Indices\Terms\TransportManagerLicenceStatus;

class Person extends AbstractIndex
{
    /** @return ComplexTermInterface[] */
    public function getFilters(): array
    {
        if (!isset($this->filters)) {
            $this->filters = [
                new TransportManagerLicenceStatus(),
            ];
        }

        return $this->filters;
    }
}
