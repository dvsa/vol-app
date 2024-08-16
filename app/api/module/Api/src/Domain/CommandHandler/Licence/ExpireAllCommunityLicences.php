<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;

/**
 * Expire All Community Licences
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ExpireAllCommunityLicences extends UpdateAllCommunityLicences
{
    protected $status = CommunityLic::STATUS_EXPIRED;
}
