<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerLicenceBundle as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Date 1st TM soft letter sent
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class Date1stTmLetterSent extends DynamicBookmark
{
    
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['transportManagerLicence']]);
    }

    public function render(): ?string
    {
        if (is_string($this->data['lastTmFirstEmailDate'])) {
            $dateTime = new \DateTime($this->data['lastTmFirstEmailDate']);
        } else {
            $dateTime = $this->data['lastTmFirstEmailDate'];
        }

        return $dateTime->format('d/m/Y');
    }
}
