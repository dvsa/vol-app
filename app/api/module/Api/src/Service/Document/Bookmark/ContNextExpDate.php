<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Continuation Next Expiry Date bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContNextExpDate extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence']]);
    }

    public function render()
    {
        if (isset($this->data['expiryDate'])) {
            $target = new \DateTime($this->data['expiryDate']);
            $target->add(new \DateInterval('P5Y'));
            return $target->format('d/m/Y');
        }
        return '';
    }
}
