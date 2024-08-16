<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Traits\DateHelperAwareTrait;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TotalContFee as Qry;

/**
 * TotalContFee bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TotalContFee extends DynamicBookmark implements DateHelperAwareInterface
{
    use DateHelperAwareTrait;

    /**
     * Request data from API
     *
     * @param array $data Parameters
     *
     * @return \Dvsa\Olcs\Transfer\Query\QueryInterface
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'goodsOrPsv' => $data['goodsOrPsv'],
                'licenceType' => $data['licenceType'],
                'effectiveFrom' => $this->getDateHelper()->getDate('Y-m-d'),
                'trafficArea' => ($data['niFlag'] === 'Y'
                    ? TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE
                    : null
                ),
            ]
        );
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->data)) {
            $value = (int) $this->data['fixedValue'] ? $this->data['fixedValue'] :
                $this->data['fiveYearValue'];
            return number_format($value);
        }
        return '';
    }
}
