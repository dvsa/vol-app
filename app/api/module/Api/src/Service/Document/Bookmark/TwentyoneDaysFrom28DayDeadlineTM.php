<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TransportManagerLicenceBundle as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Twenty one Days From 28 Day Deadline after TM Removal
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class TwentyoneDaysFrom28DayDeadlineTM extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['transportManagerLicence']]);
    }

    #[\Override]
    public function render(): ?string
    {
        if (empty($this->data['deletedDate'])) {
            return null;
        }

        if (is_string($this->data['deletedDate'])) {
            $dateTime = new \DateTime($this->data['deletedDate']);
        } else {
            $dateTime = clone $this->data['deletedDate'];
        }

        $dateTime->add(new \DateInterval('P49D'));

        return $dateTime->format('d/m/Y');
    }
}
