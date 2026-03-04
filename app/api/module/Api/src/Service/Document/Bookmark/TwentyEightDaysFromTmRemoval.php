<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerLicenceBundle as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Twenty Eight Days From TmRemoval Bookmark
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class TwentyEightDaysFromTmRemoval extends DynamicBookmark
{
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['transportManagerLicence']]);
    }

    public function render(): ?string
    {
        if (empty($this->data['deletedDate'])) {
            return null;
        } 
        
        if (is_string($this->data['deletedDate'])) {
            $dateTime = new \DateTime($this->data['deletedDate']);
        } else {
            $dateTime = $this->data['deletedDate'];
        }

        $dateTime->add(new \DateInterval('P28D'));

        return $dateTime->format('d/m/Y');
    }
}
