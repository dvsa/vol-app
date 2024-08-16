<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\OrganisationBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * IrfoTaName
 */
class IrfoTaName extends DynamicBookmark
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
                    'tradingNames' => [
                        'licence'
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
        if (!empty($this->data['tradingNames'])) {
            $tradingNames = [];

            array_map(
                function ($tradingName) use (&$tradingNames) {
                    if (empty($tradingName['licence'])) {
                        // only trading name which is not linked to a licence
                        $tradingNames[] = $tradingName['name'];
                    }
                },
                $this->data['tradingNames']
            );

            if (!empty($tradingNames)) {
                return 'T/A: ' . implode(' ', $tradingNames);
            }
        }

        return '';
    }
}
