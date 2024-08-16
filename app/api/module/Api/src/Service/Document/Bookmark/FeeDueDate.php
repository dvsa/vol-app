<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Date;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Traits\DateHelperAwareTrait;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as Qry;

/**
 * Fee due date bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeeDueDate extends DynamicBookmark implements DateHelperAwareInterface
{
    use DateHelperAwareTrait;

    public const TARGET_DAYS = 15;

    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['fee']]);
    }

    public function render()
    {
        if (is_string($this->data['invoicedDate'])) {
            $dateTime = new \DateTime($this->data['invoicedDate']);
        } else {
            $dateTime = $this->data['invoicedDate'];
        }

        $dateTime = $this->getDateHelper()->calculateDate($dateTime, self::TARGET_DAYS);

        return Date::format([$dateTime->format('Y-m-d')]);
    }
}
