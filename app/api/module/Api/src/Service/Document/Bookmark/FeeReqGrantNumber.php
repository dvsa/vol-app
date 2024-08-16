<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as Qry;

/**
 * Fee request grant number bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeeReqGrantNumber extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['fee'], 'bundle' => ['licence']]);
    }

    public function render()
    {
        return $this->data['licence']['licNo'] . ' / ' . $this->data['id'];
    }
}
