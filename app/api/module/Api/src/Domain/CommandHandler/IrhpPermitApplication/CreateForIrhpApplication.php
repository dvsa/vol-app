<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create an IRHP Permit
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class CreateForIrhpApplication extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitApplication';
    protected $extraRepos = ['IrhpPermitWindow', 'IrhpApplication'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $irhpPermitWindow =  $this->getRepo('IrhpPermitWindow')->fetchById($command->getIrhpPermitWindow());
        $irhpApplication = $this->getRepo('IrhpApplication')->fetchById($command->getIrhpApplication());

        $irhpPermitApplication = IrhpPermitApplicationEntity::createNewForIrhpApplication(
            $irhpApplication,
            $irhpPermitWindow
        );

        $this->getRepo()->save($irhpPermitApplication);

        $this->result->addId('irhpPermitApplication', $irhpPermitApplication->getId());
        $this->result->addMessage("IrhpPermitApplication Created");

        return $this->result;
    }
}
