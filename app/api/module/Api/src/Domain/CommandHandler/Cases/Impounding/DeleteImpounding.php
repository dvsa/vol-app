<?php

/**
 * Delete Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class DeleteImpounding extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Impounding';
}
