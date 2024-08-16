<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\ImageBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BrLogo extends ImageBookmark
{
    public const CONTAINER_HEIGHT = 100;

    public const IMAGE_PREFIX = 'TC_LOGO_';

    /**
     * Get query
     *
     * @param array $data data
     *
     * @return null|static
     */
    public function getQuery(array $data)
    {
        if (!isset($data['busRegId'])) {
            return null;
        }

        $bundle = [
            'licence' => [
                'trafficArea'
            ]
        ];
        return Qry::create(['id' => $data['busRegId'], 'bundle' => $bundle]);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $key = !empty($this->data['licence']['trafficArea']['isScotland']) ? 'SCOTTISH' : 'OTHER';

        return $this->getImage(
            static::IMAGE_PREFIX . $key,
            null,
            static::CONTAINER_HEIGHT
        );
    }
}
