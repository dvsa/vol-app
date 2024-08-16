<?php

/**
 * TmQualification / Delete
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * TmQualification / Delete
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'TmQualification';
}
