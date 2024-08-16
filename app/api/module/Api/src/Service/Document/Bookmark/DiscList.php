<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\GoodsDiscBundle as Qry;
use Doctrine\Common\Collections\Criteria;

/**
 * Goods Disc list bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DiscList extends AbstractDiscList
{
    /**
     * Discs per row in a page
     */
    public const PER_ROW = 2;

    /**
     * Typical row spacer. Magic number gleaned from old codebase
     */
    public const ROW_HEIGHT = 2526;

    /**
     * Last row spacer. Magic number gleaned from old codebase
     */
    public const LAST_ROW_HEIGHT = 359;

    /**
     * Bookmark variable prefix
     */
    public const BOOKMARK_PREFIX = 'DISC';

    protected $discBundle = [
        'licenceVehicle' => [
            'licence' => [
                'tradingNames',
                'organisation'
            ],
            'vehicle',
            'interimApplication'
        ]
    ];

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        foreach ($this->data as $key => $disc) {
            $licence = $disc['licenceVehicle']['licence'];
            $vehicle = $disc['licenceVehicle']['vehicle'];
            $organisation = $licence['organisation'];

            // split the org over multiple lines if necessary
            $orgParts = $this->splitString($organisation['name']);

            // we want all trading names as one comma separated array...
            $tradingNames = $this->implodeNames($licence['tradingNames']);
            // ... before worrying about whether to split them into different bookmark lines
            $tradingParts = $this->splitString($tradingNames);

            $prefix = $this->getPrefix($key);

            if (isset($disc['licenceVehicle']['interimApplication']['id'])) {
                $discTitle = 'INTERIM';
            } elseif ($disc['isCopy'] === 'Y') {
                $discTitle = 'COPY';
            } else {
                $discTitle = '';
            }
            $discLicenceId = $licence['licNo'];
            if (isset($disc['licenceVehicle']['interimApplication']['id'])) {
                $discLicenceId .= ' START ' . $disc['licenceVehicle']['interimApplication']['interimStart'];
            }
            $discs[] = [
                $prefix . 'TITLE'       => $discTitle,
                $prefix . 'DISC_NO'     => $disc['discNo'],
                $prefix . 'LINE1'       => $orgParts[0] ?? '',
                $prefix . 'LINE2'       => $orgParts[1] ?? '',
                $prefix . 'LINE3'       => $orgParts[2] ?? '',
                $prefix . 'LINE4'       => $tradingParts[0] ?? '',
                $prefix . 'LINE5'       => $tradingParts[1] ?? '',
                $prefix . 'LICENCE_ID'  => $discLicenceId,
                $prefix . 'VEHICLE_REG' => $vehicle['vrm'],
                $prefix . 'EXPIRY_DATE' => isset($licence['expiryDate'])
                    ? $this->formatDate($licence['expiryDate'])
                    : 'N/A'
            ];
        }

        /**
         * We always want a full page of discs, even if we have to
         * fill the rest up with placeholders
         */
        while (($length = count($discs) % self::PER_PAGE) !== 0) {
            $prefix = $this->getPrefix($length);
            $discs[] = [
                $prefix . 'TITLE'       => self::PLACEHOLDER,
                $prefix . 'DISC_NO'     => self::PLACEHOLDER,
                $prefix . 'LINE1'       => self::PLACEHOLDER,
                $prefix . 'LINE2'       => self::PLACEHOLDER,
                $prefix . 'LINE3'       => self::PLACEHOLDER,
                $prefix . 'LINE4'       => self::PLACEHOLDER,
                $prefix . 'LINE5'       => self::PLACEHOLDER,
                $prefix . 'LICENCE_ID'  => self::PLACEHOLDER,
                $prefix . 'VEHICLE_REG' => self::PLACEHOLDER,
                $prefix . 'EXPIRY_DATE' => self::PLACEHOLDER
            ];
        }

        // bit ugly, but now we have to chunk the discs into N per row
        $discGroups = [];
        for ($i = 0; $i < count($discs); $i += self::PER_ROW) {
            $discGroups[] = array_merge(
                $discs[$i],
                $discs[$i + 1],
                [
                    'ROW_HEIGHT' => $this->getRowHeight($i)
                ]
            );
        }

        return $this->renderSnippets($discGroups);
    }

    /*
     * Take an array of arrays with a name value and return them
     * as a comma separated string instead
     */
    private function implodeNames($names)
    {
        return implode(
            ", ",
            array_map(
                fn($val) => $val['name'],
                $names
            )
        );
    }

    /**
     * Helper to work out our row height. Yuck
     */
    private function getRowHeight($index)
    {
        $index /= self::PER_ROW;
        $max = self::PER_PAGE / self::PER_ROW;

        return ($index % $max === $max - 1) ? self::LAST_ROW_HEIGHT : self::ROW_HEIGHT;
    }

    protected function getQueryClass(): string
    {
        return Qry::class;
    }
}
