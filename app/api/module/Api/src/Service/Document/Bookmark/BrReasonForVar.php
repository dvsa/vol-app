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
class BrReasonForVar extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['busRegId'], 'bundle' => ['variationReasons']]);
    }

    public function render()
    {
        $localAuthoritys = implode(
            ', ',
            array_map(
                fn($item) => $item['description'],
                $this->data['variationReasons']
            )
        );

        return $localAuthoritys;
    }
}
