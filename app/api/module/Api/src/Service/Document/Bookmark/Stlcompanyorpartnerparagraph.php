<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Stlcompanyorpartnerparagraph bookmark
 */
class Stlcompanyorpartnerparagraph extends AbstractLicenceType
{
    public const PARTNERSHIP_ORGANISATION_TYPE_IDS = [Organisation::ORG_TYPE_PARTNERSHIP, Organisation::ORG_TYPE_LLP];

    /**
     * Get the query to fetch data for the bookmark
     *
     * @param array $data Known data
     *
     * @return Qry
     */
    #[\Override]
    public function getQuery(array $data)
    {
        $queryClass = static::QUERY_CLASS;
        return $queryClass::create(['id' => $data[static::DATA_KEY], 'bundle' => ['organisation' => ['type']]]);
    }

    /**
     * Render the bookmark
     *
     * @return null|string
     */
    #[\Override]
    public function render()
    {
        $organisationTypeId = $this->data['organisation']['type']['id'];

        if ($organisationTypeId === Organisation::ORG_TYPE_REGISTERED_COMPANY) {
            return $this->getSnippet('Stlcompanyorpartnerparagraph/ltd');
        }

        if (in_array($organisationTypeId, self::PARTNERSHIP_ORGANISATION_TYPE_IDS, true)) {
            return $this->getSnippet('Stlcompanyorpartnerparagraph/llp');
        }

        return null;
    }
}
