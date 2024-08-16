<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * UpdateDeclarationsInternalStatus
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateDeclarationsInternalStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'DeclarationsInternal';

    protected function isSectionValid(Application $application)
    {
        return $application->getAuthSignature();
    }
}
