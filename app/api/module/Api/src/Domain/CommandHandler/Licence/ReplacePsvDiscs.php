<?php

/**
 * Replace Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs as CreatePsvDiscsCmd;
use Dvsa\Olcs\Transfer\Command\Licence\VoidPsvDiscs as VoidPsvDiscsCmd;

/**
 * Replace Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReplacePsvDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    // @NOTE Don't need repo
    protected $repoServiceName = '';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $result->merge(
            $this->handleSideEffect(VoidPsvDiscsCmd::create($command->getArrayCopy()))
        );

        $dtoData = [
            'licence' => $command->getLicence(),
            'amount' => count($command->getIds()),
            'isCopy' => 'Y'
        ];

        $result->merge(
            $this->handleSideEffect(CreatePsvDiscsCmd::create($dtoData))
        );

        return $result;
    }
}
