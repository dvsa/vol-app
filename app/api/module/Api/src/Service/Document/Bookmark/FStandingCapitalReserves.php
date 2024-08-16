<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Traits\DateHelperAwareTrait;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FStandingCapitalReserves as Qry;

/**
 * F_Standing_CapitalReserves bookmark
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FStandingCapitalReserves extends DynamicBookmark implements DateHelperAwareInterface
{
    use DateHelperAwareTrait;

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'organisation' => $data['organisation'],
            ]
        );
    }

    public function render()
    {
        return number_format($this->data, 0);
    }
}
