<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\OppositionBundle as Qry;

/**
 * Applicant name bookmark
 */
class ApplicantName extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['opposition'])) {
            return null;
        }

        $bundle = [
            'licence' => [
                'organisation' => [
                    'tradingNames'
                ]
            ]
        ];
        return Qry::create(['id' => $data['opposition'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (isset($this->data['licence']['organisation'])) {
            return Formatter\OrganisationName::format($this->data['licence']['organisation']);
        }
        return '';
    }
}
