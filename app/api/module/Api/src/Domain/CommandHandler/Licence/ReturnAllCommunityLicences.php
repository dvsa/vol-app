<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;

/**
 * Return All Community Licences
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ReturnAllCommunityLicences extends UpdateAllCommunityLicences
{
    protected $status = CommunityLic::STATUS_RETURNDED;
}
