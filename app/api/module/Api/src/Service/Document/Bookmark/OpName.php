<?php

/**
 * Op Name
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Op Name
 *
 * Returns the operator's name and address and associated contact information.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OpName extends DynamicBookmark
{
    /**
     * Get the query, this query returns the operator's details.
     *
     * @param array $data The licence data
     *
     * @return array The query array.
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'organisation' => [
                'tradingNames'
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Return the operator's name, company name, trading name(s) and address.
     *
     * @return string The operator's address.
     */
    public function render()
    {
        $organisation = $this->data['organisation'];

        $tradingNames = '';
        array_map(
            function ($tradingName) use (&$tradingNames) {
                $tradingNames .= $tradingName['name'] . ' ';
            },
            $organisation['tradingNames']
        );

        if (strlen($tradingNames) > 0) {
            $tradingNames = substr($tradingNames, 0, -1);
            $tradingNames = 'T/A: ' . substr($tradingNames, 0, 40);
        }

        return implode(
            "\n",
            array_filter(
                [
                    $organisation['name'],
                    $tradingNames
                ]
            )
        );
    }
}
