<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Operator 'FAO' bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OpFaoName extends DynamicBookmark
{
    protected $params = ['licence'];

    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence'], 'bundle' => ['correspondenceCd']]);
    }

    public function render()
    {
        return $this->data['correspondenceCd']['fao'] ?? '';
    }
}
