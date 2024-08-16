<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FeeBundle as FeeQry;

/**
 * FeeReqNumber
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeReqNumber extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return FeeQry::create(['id' => $data['fee'], 'bundle' => ['licence']]);
    }

    public function render()
    {
        return $this->data['licence']['licNo'] . '/' . $this->data['id'];
    }
}
