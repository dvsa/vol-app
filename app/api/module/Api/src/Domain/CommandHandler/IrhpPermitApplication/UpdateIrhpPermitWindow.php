<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Window on IRHP Permit Application
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class UpdateIrhpPermitWindow extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitApplication';
    protected $extraRepos = ['IrhpPermitWindow'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {

        /* @var $irhpPermitApplicationRepo IrhpPermitApplication */
        $irhpPermitApplicationRepo = $this->getRepo();
        $irhpPermitApplication = $irhpPermitApplicationRepo->fetchById($command->getId());

        $irhpPermitWindow =  $this->getRepo('IrhpPermitWindow')->fetchById($command->getIrhpPermitWindow());

        $irhpPermitApplication->updateIrhpPermitWindow($irhpPermitWindow);

        $irhpPermitApplicationRepo->save($irhpPermitApplication);

        $this->result->addId('irhpPermitApplication', $irhpPermitApplication->getId());
        $this->result->addMessage("IrhpPermitApplication Updated");

        return $this->result;
    }
}
