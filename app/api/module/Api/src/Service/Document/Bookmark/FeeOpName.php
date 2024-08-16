<?php

/**
 * Fee Op Name
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Fee Op Name
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeOpName extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'organisation' => [
                'tradingNames'
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        $organisation = $this->data['organisation'];

        $tradingNames = '';
        array_map(
            function ($tradingName) use (&$tradingNames) {
                $tradingNames .= $tradingName['name'] . ', ';
            },
            $organisation['tradingNames']
        );

        $tradingNames = trim($tradingNames, ', ');
        if (strlen($tradingNames) > 0) {
            $tradingNames = 'TA ' . $tradingNames;
            if (strlen($tradingNames) > 40) {
                $tradingNames = substr($tradingNames, 0, 37) . '...';
            }
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
