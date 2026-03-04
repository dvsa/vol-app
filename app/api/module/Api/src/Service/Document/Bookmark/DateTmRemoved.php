<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerLicenceBundle as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Transport manager removed date bookmark
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class DateTmRemoved extends DynamicBookmark
{
    
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['transportManagerLicence']]);
    }

    public function render(): ?string
    {
        if (is_string($this->data['deletedDate'])) {
            $dateTime = new \DateTime($this->data['deletedDate']);
        } else {
            $dateTime = $this->data['deletedDate'];
        }

        return $dateTime->format('d/m/Y');
    }
}
