<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Two Weeks Before bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TwoWeeksBefore extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence']]);
    }

    public function render()
    {
        if (isset($this->data['expiryDate'])) {
            $target = new \DateTime($this->data['expiryDate']);
            $interval = new \DateInterval('P14D');
            $interval->invert = 1;
            $target->add($interval);
            return $target->format('d/m/Y');
        }
        return '';
    }
}
