<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Trading names bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TradingNames extends DynamicBookmark
{
    /**
     * Get the DTO that will get data required by this bookmark
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Query\QueryInterface
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'tradingNames'
        ];

        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Rendered bookmark
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->data['tradingNames'])) {
            $names = [];
            foreach ($this->data['tradingNames'] as $tn) {
                $names[] = $tn['name'];
            }
            return implode(', ', $names);
        }
        return '';
    }
}
