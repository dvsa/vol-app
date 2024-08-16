<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * IrfoAddress
 */
class IrfoAddress extends DynamicBookmark
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
                    'irfoContactDetails' => [
                        'address' => [
                            'countryCode'
                        ]
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
        if (isset($this->data['irfoContactDetails']['address'])) {
            $addressFormatter = new Formatter\Address();
            $addressFormatter->setSeparator(', ');
            return $addressFormatter->format($this->data['irfoContactDetails']['address']);
        }

        return '';
    }
}
