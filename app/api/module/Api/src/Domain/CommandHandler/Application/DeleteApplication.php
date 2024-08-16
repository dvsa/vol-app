<?php

/**
 * DeleteApplication.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Class DeleteApplication
 *
 * Delete an application.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Application
 */
final class DeleteApplication extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $application Application */
        $application = $this->getRepo()->fetchUsingId($command);

        // safety measure, currently only variations not submitted can be deleted
        if (
            $application->isVariation() &&
            $application->getStatus()->getId() === Application::APPLICATION_STATUS_NOT_SUBMITTED
        ) {
            $this->getRepo()->delete($application);
        } else {
            throw new Exception\BadRequestException('Only Not Submitted Variations can be deleted.');
        }

        $result->addMessage('Application ' . $application->getId() . ' deleted.');

        return $result;
    }
}
