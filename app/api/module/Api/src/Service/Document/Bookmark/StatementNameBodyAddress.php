<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Name as NameFormatter;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Address as AddressFormatter;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\StatementBundle as Qry;

/**
 * StatementNameBodyAddress bookmark
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class StatementNameBodyAddress extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['statement'])) {
            return null;
        }

        $bundle = [
            'requestorsContactDetails' => [
                'person',
                'address',
            ]
        ];
        return Qry::create(['id' => $data['statement'], 'bundle' => $bundle]);
    }

    public function render()
    {
        $person = $this->data['requestorsContactDetails']['person'];
        $address = $this->data['requestorsContactDetails']['address'] ?? [];

        $separator = "\n";

        $string = implode(
            $separator,
            array_filter(
                [
                    NameFormatter::format($person),
                    $this->data['requestorsBody'],
                    AddressFormatter::format($address)
                ]
            )
        );

        return $string;
    }
}
