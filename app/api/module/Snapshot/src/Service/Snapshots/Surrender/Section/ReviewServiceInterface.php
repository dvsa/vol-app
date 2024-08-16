<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;

interface ReviewServiceInterface
{
    /**
     * Format the readonly config from the given record
     *
     * @return mixed
     */
    public function getConfigFromData(Surrender $surrender);
}
