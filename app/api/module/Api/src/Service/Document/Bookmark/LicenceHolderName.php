<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence holder name bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceHolderName extends DynamicBookmark
{
    protected $params = ['licence'];

    public const MAX_TRADING_NAME_LINE_LENGTH = 40;

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['licence'],
                'bundle' => [
                    'tradingNames',
                    'organisation',
                ],
            ]
        );
    }

    public function render()
    {
        $name = $this->data['organisation']['name'];

        if (!empty($this->data['tradingNames'])) {
            $tradingNames = array_map(
                fn($tradingName) => $tradingName['name'],
                $this->data['tradingNames']
            );
            $tradingAs = sprintf("\nT/A %s", implode(', ', $tradingNames));
            $name .= substr($tradingAs, 0, self::MAX_TRADING_NAME_LINE_LENGTH);
        }

        return $name;
    }
}
