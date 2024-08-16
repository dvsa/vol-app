<?php

/**
 * Review Service Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;

/**
 * Review Service Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ReviewServiceInterface
{
    /**
     * Format the readonly config from the given record
     *
     * @return mixed
     */
    public function getConfig(TransportManagerApplication $tma);
}
