<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * OpCorrespondenceAddress - fetch corresponding Licence address
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class OpCorrespondenceAddress extends DynamicBookmark
{
    /**
     * Gets query to retrieve data
     *
     * @param array $data
     * @return Qry|null
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['organisation'],
                'bundle' => [
                    'contactDetails' => [
                        'address'
                    ]
                ]
            ]
        );
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->data['contactDetails']['address'])) {
            $addressFormatter = new Formatter\Address();
            $addressFormatter->setSeparator(', ');
            return $addressFormatter->format($this->data['contactDetails']['address']);
        }

        return '';
    }
}
