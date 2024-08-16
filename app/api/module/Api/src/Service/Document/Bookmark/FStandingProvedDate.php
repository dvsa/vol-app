<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as LicenceQry;

/**
 * Financial Standing proved date
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FStandingProvedDate extends DynamicBookmark
{
    /**
     * Get query
     *
     * @param array $data data
     *
     * @return mixed
     */
    public function getQuery(array $data)
    {
        return LicenceQry::create(['id' => $data['licence']]);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->data['expiryDate'])) {
            /**
             * need to take last day of previous month
             * if we just subtract 1 month from the expiry date in could be the same month
             * @see http://php.net/manual/en/datetime.sub.php
             * so let's take 15th of the current month as a day to be sure
             */
            $target = new \DateTime($this->data['expiryDate']);
            $target->setDate($target->format('Y'), $target->format('m'), 15);
            $target->sub(new \DateInterval('P1M'));

            return $target->format('t/m/Y');
        }
        return '';
    }
}
