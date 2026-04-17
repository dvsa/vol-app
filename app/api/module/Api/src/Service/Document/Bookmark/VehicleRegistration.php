<?php

/**
 * Vehicle Registration
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\VehicleBundle as Qry;

/**
 * Vehicle Registration
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleRegistration extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['vehicle'], 'bundle' => []]);
    }

    #[\Override]
    public function render()
    {
        return $this->data['vrm'];
    }
}
