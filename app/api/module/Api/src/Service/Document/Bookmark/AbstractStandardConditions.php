<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as LicenceQry;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle as ApplicationQry;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Standard Conditions bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractStandardConditions extends DynamicBookmark
{
    /**
     * Let the parser know we've already formatted our content by the
     * time it has been rendered
     */
    public const PREFORMATTED = true;

    public const SERVICE = 'licence';

    public const DATA_KEY = 'licence';

    protected $prefix = '';

    /**
     * Create Query
     *
     * @param array $data Query Parameters
     *
     * @return null|QueryInterface
     */
    public function getQuery(array $data)
    {
        if (static::SERVICE === 'licence') {
            return LicenceQry::create(['id' => $data[static::DATA_KEY]]);
        } elseif (static::SERVICE === 'application') {
            return ApplicationQry::create(['id' => $data[static::DATA_KEY]]);
        }
        return null;
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $type = '';

        switch ($this->data['licenceType']['id']) {
            case Licence::LICENCE_TYPE_RESTRICTED:
                $type = 'RESTRICTED';
                break;

            case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                $type = 'STANDARD';
                break;

            case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                $type = 'STANDARD_INT';
                if ($this->data['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV) {
                    $type .= '_LGV';
                }
                break;
        }

        $path = $this->prefix . '_' . $type . '_LICENCE_CONDITIONS';

        return $this->getSnippet($path);
    }
}
