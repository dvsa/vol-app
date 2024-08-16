<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrRouteNum extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['busRegId'])) {
            return null;
        }

        return Qry::create(['id' => $data['busRegId'], 'bundle' => ['otherServices']]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $value = $this->data['serviceNo'];

        $otherServices
            = !empty($this->data['otherServices']) ?
                implode(', ', array_column($this->data['otherServices'], 'serviceNo')) : null;

        if (!empty($otherServices)) {
            $value .= sprintf(" (%s)", $otherServices);
        }

        return $value;
    }
}
