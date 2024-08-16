<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddMonthsRoundingDown;

/**
 * ReviewDateAdd2Month bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ReviewDateAdd2Months extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence']]);
    }

    public function render()
    {
        if (isset($this->data['reviewDate'])) {
            $addMonths = new AddMonthsRoundingDown();
            $target = $addMonths->calculateDate(new \DateTime($this->data['reviewDate']), 2);
            return $target->format('d/m/Y');
        }
        return '';
    }
}
