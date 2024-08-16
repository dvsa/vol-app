<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CommunityLicBundle as Qry;

/**
 * European Licence Number bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class EuropeanLicenceNumber extends DynamicBookmark
{
    public const ISSUE_NO_PAD_LENGTH = 5;

    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['communityLic'], 'bundle' => ['licence']]);
    }

    public function render()
    {
        $issueNo = str_pad((string) $this->data['issueNo'], self::ISSUE_NO_PAD_LENGTH, '0', STR_PAD_LEFT);

        return $this->data['licence']['licNo'] . '/' . $issueNo;
    }
}
