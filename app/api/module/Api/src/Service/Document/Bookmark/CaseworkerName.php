<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UserBundle as Qry;

/**
 * Caseworker name bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CaseworkerName extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'contactDetails' => [
                'person'
            ]
        ];
        return Qry::create(['id' => $data['user'], 'bundle' => $bundle]);
    }

    public function render()
    {
        return Formatter\Name::format($this->data['contactDetails']['person']);
    }
}
