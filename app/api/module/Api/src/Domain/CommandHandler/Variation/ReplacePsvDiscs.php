<?php

/**
 * Replace Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Licence\ReplacePsvDiscs as LicenceReplacePsvDiscs;

/**
 * Replace Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReplacePsvDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $id = $command->getApplication();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchById($id);

        $data = $command->getArrayCopy();
        $data['licence'] = $application->getLicence()->getId();
        $result->merge($this->handleSideEffect(LicenceReplacePsvDiscs::create($data)));

        $dtoData = ['id' => $id, 'section' => 'discs'];
        $result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($dtoData)));

        return $result;
    }
}
