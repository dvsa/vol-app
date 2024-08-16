<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * BkmTelephone
 */
class BkmTelephone extends DynamicBookmark
{
    /**
     * Gets query to retrieve data
     *
     * @param array $data Known data
     *
     * @return Qry|null
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['organisation'],
                'bundle' => [
                    'irfoContactDetails' => [
                        'phoneContacts' => [
                            'phoneContactType'
                        ],
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
        if (empty($this->data['irfoContactDetails']['phoneContacts'])) {
            return '';
        }

        return Formatter\ContactNumber::format($this->data['irfoContactDetails']['phoneContacts']);
    }
}
