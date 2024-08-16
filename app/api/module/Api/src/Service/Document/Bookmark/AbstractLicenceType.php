<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Class LicenceType
 *
 * Concatenates the licence types and category descriptions together in order to generate
 * a full licence type string.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractLicenceType extends DynamicBookmark
{
    public const QUERY_CLASS  = Qry::class;
    public const DATA_KEY = 'licence';

    /**
     * Returns the bundle query to be used in the REST call to the backend.
     *
     * @param array $data Data to be used within the query.
     *
     * @return array The full query array.
     */
    public function getQuery(array $data)
    {
        $queryClass = static::QUERY_CLASS;
        return $queryClass::create(['id' => $data[static::DATA_KEY]]);
    }

    /**
     * The render method to be used for this bookmark. This method returns the
     * types and categories as one string
     *
     * @return string
     */
    public function render()
    {
        $goodsOrPsvData = $this->data['goodsOrPsv'];
        $licenceTypeData = $this->data['licenceType'];

        return $goodsOrPsvData['description'] . " " . $licenceTypeData['description'];
    }
}
