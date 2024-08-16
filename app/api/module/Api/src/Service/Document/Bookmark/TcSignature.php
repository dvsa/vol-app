<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\ImageBookmark;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TA;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Traffic Comissioner signature
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TcSignature extends ImageBookmark
{
    public const PREFORMATTED = true;

    /**
     * Hard-coding our containing element's dimensions is far from
     * ideal but it's the only way to make all the (very differently sized)
     * images render consistently. We could avoid this using the OLBS style
     * INCLUDEPICTURE <path> but ONLY where <path> is a filesystem, i.e.
     * \\networkdrive\path\to\image.jpg. Using INCLUDEPICTURE with URLs
     * does NOT resize the image
     */
    public const CONTAINER_WIDTH = 251;
    public const CONTAINER_HEIGHT = 56;

    public const IMAGE_PREFIX = 'TC_SIG_';

    private $imageMap = [
        TA::NORTH_EASTERN_TRAFFIC_AREA_CODE    => 'NORTHEASTERN',
        TA::NORTH_WESTERN_TRAFFIC_AREA_CODE    => 'NORTHWESTERN',
        TA::WEST_MIDLANDS_TRAFFIC_AREA_CODE    => 'WESTMIDLANDS',
        TA::EASTERN_TRAFFIC_AREA_CODE          => 'EASTERN',
        TA::WELSH_TRAFFIC_AREA_CODE            => 'WELSH',
        TA::WESTERN_TRAFFIC_AREA_CODE          => 'WESTERN',
        TA::SE_MET_TRAFFIC_AREA_CODE           => 'SE_MET',
        TA::SCOTTISH_TRAFFIC_AREA_CODE         => 'SCOTTISH',
        TA::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => 'NORTHERNIRELAND'
    ];

    public function getQuery(array $data)
    {
        $bundle = [
            'trafficArea'
        ];

        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        $key = $this->data['trafficArea']['id'];

        return $this->getImage(
            static::IMAGE_PREFIX . $this->imageMap[$key],
            static::CONTAINER_WIDTH,
            static::CONTAINER_HEIGHT
        );
    }
}
