<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrfoPsvAuthBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * BkmOperatorAddress1
 */
class BkmOperatorAddress1 extends DynamicBookmark
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
                'id' => $data['irfoPsvAuth'],
                'bundle' => [
                    'organisation' => [
                        'irfoContactDetails' => [
                            'address' => [
                                'countryCode'
                            ]
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
        if (isset($this->data['organisation']['irfoContactDetails']['address'])) {
            return Formatter\Address::format($this->data['organisation']['irfoContactDetails']['address']);
        }

        return '';
    }
}
