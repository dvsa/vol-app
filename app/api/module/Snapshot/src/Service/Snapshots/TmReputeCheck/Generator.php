<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;

class Generator extends AbstractGenerator
{
    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    public function generate()
    {
    }
}
