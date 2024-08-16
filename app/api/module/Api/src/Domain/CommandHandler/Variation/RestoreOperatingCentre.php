<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteOperatingCentre as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as HandleOcVariationFeesCmd;

/**
 * Restore Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class RestoreOperatingCentre extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre', 'LicenceOperatingCentre'];

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $applicationId = $command->getApplication();

        [$prefix, $id] = $this->splitTypeAndId($command->getId());

        if ($prefix === 'A') {

            /** @var ApplicationOperatingCentre $aoc */
            $aoc = $this->getRepo('ApplicationOperatingCentre')->fetchById($id);

            if ($aoc->getAction() === 'D') {
                $this->getRepo('ApplicationOperatingCentre')->delete($aoc);
                $this->result->addMessage('Delta record removed');

                $completionData = ['id' => $command->getApplication(), 'section' => 'operatingCentres'];
                $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));
                $this->result->merge(
                    $this->handleSideEffect(HandleOcVariationFeesCmd::create(['id' => $applicationId]))
                );

                return $this->result;
            }

            throw new ForbiddenException('Can\'t restore this record');
        }

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchById($command->getApplication());

        /** @var LicenceOperatingCentre $loc */
        $loc = $this->getRepo('LicenceOperatingCentre')->fetchById($id);

        $oc = $loc->getOperatingCentre();

        $deltaRecords = $application->getDeltaAocByOc($oc);

        if ($deltaRecords->isEmpty()) {
            throw new ForbiddenException('Can\'t restore this record');
        }

        foreach ($deltaRecords as $aoc) {
            $this->getRepo('ApplicationOperatingCentre')->delete($aoc);
        }

        $this->result->addMessage($deltaRecords->count() . ' Delta record(s) removed');

        $completionData = ['id' => $application->getId(), 'section' => 'operatingCentres'];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));
        $this->result->merge(
            $this->handleSideEffect(HandleOcVariationFeesCmd::create(['id' => $applicationId]))
        );

        return $this->result;
    }

    private function splitTypeAndId($ref)
    {
        $type = substr((string) $ref, 0, 1);

        $id = (int)substr((string) $ref, 1);

        return [$type, $id];
    }
}
