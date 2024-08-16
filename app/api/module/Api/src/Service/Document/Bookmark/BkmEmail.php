<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * BkmEmail
 */
class BkmEmail extends DynamicBookmark
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
                    'irfoContactDetails'
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
        return !empty($this->data['irfoContactDetails']['emailAddress'])
            ? $this->data['irfoContactDetails']['emailAddress'] : '';
    }
}
