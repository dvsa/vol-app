<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Check Answers
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class UpdateCheckAnswers extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpApplication';
    protected $extraRepos = ['IrhpPermitApplication'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function handleCommand(CommandInterface $command)
    {
        $repo = $this->getRepo();

        $irhpApplication = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT);

        if ($irhpApplication->isBilateral()) {
            $repo = $this->getRepo('IrhpPermitApplication');

            $irhpPermitApplication = $repo->fetchById($command->getIrhpPermitApplication());

            if ($irhpPermitApplication->getIrhpApplication() !== $irhpApplication) {
                throw new NotFoundException('Mismatched IrhpApplication and IrhpPermitApplication');
            }

            $irhpPermitApplication->updateCheckAnswers();
            $repo->save($irhpPermitApplication);

            $this->result->addId('IrhpPermitApplication', $irhpPermitApplication->getId());
        } else {
            $irhpApplication->updateCheckAnswers();
            $repo->save($irhpApplication);

            $this->result->addId('IrhpApplication', $irhpApplication->getId());
        }

        $this->result->addMessage('Check Answers updated');

        return $this->result;
    }
}
