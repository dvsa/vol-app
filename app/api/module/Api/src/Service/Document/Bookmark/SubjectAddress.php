<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\OppositionBundle as Qry;

/**
 * Subject address bookmark
 */
class SubjectAddress extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['opposition'])) {
            return null;
        }

        $bundle = [
            'opposer' => [
                'contactDetails' => [
                    'address'
                ]
            ]
        ];

        return Qry::create(['id' => $data['opposition'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (isset($this->data['opposer']['contactDetails']['address'])) {
            return Formatter\Address::format($this->data['opposer']['contactDetails']['address']);
        }
        return '';
    }
}
