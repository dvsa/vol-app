<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Registered Number bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RegisteredNumber extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['licence'], 'bundle' => ['organisation']]);
    }

    #[\Override]
    public function render()
    {
        return $this->data['organisation']['companyOrLlpNo'] ?? '';
    }
}
