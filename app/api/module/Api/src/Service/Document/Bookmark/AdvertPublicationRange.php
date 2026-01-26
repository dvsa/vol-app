<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle as Qry;

/**
 * Advert Publication Range
 *
 * Returns the publication date range based on application receivedDate.
 * Calculates receivedDate - 21 days to receivedDate + 21 days.
 * Format: DD/MM/YYYY - DD/MM/YYYY
 */
class AdvertPublicationRange extends DynamicBookmark
{
    public const PARAM_APPLICATION = 'application';
    public const DAYS_DELTA = 21;

    protected $params = [self::PARAM_APPLICATION];

    /**
     * Get the query for application data
     *
     * @param array $data The context data containing application ID
     * @return Qry The query for fetching application
     */
    public function getQuery(array $data)
    {
        return Qry::create([
            'id' => $data[self::PARAM_APPLICATION]
        ]);
    }

    /**
     * Render the advert publication date range
     *
     * @return string Date range in DD/MM/YYYY - DD/MM/YYYY format, or empty string if no received date
     */
    public function render()
    {
        if (empty($this->data['receivedDate'])) {
            return '';
        }

        $receivedDate = new \DateTime($this->data['receivedDate']);

        $startDate = (clone $receivedDate)->sub(new \DateInterval('P' . self::DAYS_DELTA . 'D'));
        $endDate = (clone $receivedDate)->add(new \DateInterval('P' . self::DAYS_DELTA . 'D'));

        return $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
    }
}
