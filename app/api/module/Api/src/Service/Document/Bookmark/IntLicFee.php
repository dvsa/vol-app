<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as Qry;

/**
 * Interim Licence Fee bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IntLicFee extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['fee']]);
    }

    public function render()
    {
        if (isset($this->data['amount'])) {
            return number_format($this->data['amount']);
        }
        return '';
    }
}
