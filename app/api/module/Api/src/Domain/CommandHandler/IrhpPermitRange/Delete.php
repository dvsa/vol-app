<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete an IRHP Permit Range
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'IrhpPermitRange';
    protected $extraError = 'irhp-permit-range-cannot-delete-active-dependencies';
}
