<?php

/**
 * Op Details
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Op Details
 *
 * Returns the operator's name and address and associated contact information.
 *
 * @author Josh Curtis <josh.curtis@valtech.com>
 */
class OpDetails extends DynamicBookmark
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
            'correspondenceCd' => [
                'address'
            ],
            'organisation',
            'tradingNames',
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

        $operator = $this->data['correspondenceCd'];

        $tradingNames = '';
        array_map(
            function ($tradingName) use (&$tradingNames) {
                $tradingNames .= $tradingName['name'] . ' ';
            },
            $this->data['tradingNames']
        );

        if (strlen($tradingNames) > 0) {
            $tradingNames = 'T/A: ' . substr($tradingNames, 0, 40);
        }

        return implode(
            "\n",
            array_filter(
                [
                    $operator['fao'],
                    $organisation['name'],
                    $tradingNames,
                    Formatter\Address::format($operator['address'])
                ]
            )
        );
    }
}
