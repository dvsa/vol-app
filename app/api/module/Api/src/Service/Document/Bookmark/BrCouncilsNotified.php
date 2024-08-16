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
class BrCouncilsNotified extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['busRegId'])) {
            return null;
        }

        return Qry::create(['id' => $data['busRegId'], 'bundle' => ['localAuthoritys']]);
    }

    public function render()
    {
        $localAuthoritys = implode(
            ', ',
            array_map(
                fn($item) => $item['description'],
                $this->data['localAuthoritys']
            )
        );

        return $localAuthoritys;
    }
}
