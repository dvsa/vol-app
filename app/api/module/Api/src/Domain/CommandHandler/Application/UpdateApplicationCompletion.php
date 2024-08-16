<?php

/**
 * Update Application Completion
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\UpdateCompletion as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateVariationCompletion as VariationCommand;

/**
 * Update Application Completion
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateApplicationCompletion extends AbstractCommandHandler implements
    TransactionedInterface,
    \Dvsa\Olcs\Api\Domain\AuthAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['DigitalSignature'];

    private $sections = [
        'addresses',
        'businessDetails',
        'businessType',
        'communityLicences',
        'conditionsUndertakings',
        'convictionsPenalties',
        'discs',
        'financialEvidence',
        'financialHistory',
        'licenceHistory',
        'operatingCentres',
        'people',
        'safety',
        'taxiPhv',
        'transportManagers',
        'typeOfLicence',
        'undertakings',
        'vehiclesDeclarations',
        'vehiclesPsv',
        'vehicles',
        'declarationsInternal',
    ];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $application Application  */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        // if Variation then run UpdateVariationCompletion command
        if ($application->getIsVariation()) {
            return $this->proxyCommand($command, VariationCommand::class);
        }

        $completion = $application->getApplicationCompletion();
        // If not declaration section and selfserve user changed an under consideration application
        if (
            $command->getSection() !== 'undertakings'
            && $command->getSection() !== 'declarationsInternal'
            && $application->isNotSubmitted()
            && $this->isGranted(\Dvsa\Olcs\Api\Entity\User\Permission::SELFSERVE_USER)
        ) {
            $application->setDeclarationConfirmation('N');
            $application->setSignatureType(null);
            $digitalSignature = $application->getDigitalSignature();
            if ($digitalSignature !== null) {
                $application->setDigitalSignature(null);
                $this->getRepo('DigitalSignature')->delete($digitalSignature);
            }
        }

        $sectionsToUpdate = $this->getSectionsToUpdate($command, $completion);

        $result = new Result();

        foreach (array_keys($sectionsToUpdate) as $section) {
            $result->merge($this->handleSideEffect($this->getUpdateCommand($section, $command)));
        }

        return $result;
    }

    /**
     * Get the DTO command to update a section
     *
     * @param string $section Section name to update
     * @param Cmd    $command UpdateApplicationCompletion command
     *
     * @return CommandInterface
     */
    private function getUpdateCommand($section, Cmd $command)
    {
        $commandName = '\Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\Update' . ucfirst($section) . 'Status';

        return $commandName::create(['id' => $command->getId()]);
    }

    /**
     * Filter out the sections that we need to validate and update
     *
     * @param Cmd                   $command    UpdateApplicationCompletion command
     * @param ApplicationCompletion $completion Application completion
     *
     * @return array
     */
    private function getSectionsToUpdate(Cmd $command, ApplicationCompletion $completion)
    {
        $sectionsToUpdate = [];

        foreach ($this->sections as $section) {
            $status = (int)$completion->{'get' . ucfirst((string) $section) . 'Status'}();
            if ($this->shouldUpdateSection($section, $command, $status)) {
                $sectionsToUpdate[$section] = $status;
            }
        }

        return $sectionsToUpdate;
    }

    /**
     * Check if we should be updating the section
     *
     * @param string $section Section name
     * @param Cmd    $command UpdateApplicationCompletion command
     * @param int    $status  Current status of teh section
     *
     * @return bool
     */
    private function shouldUpdateSection($section, Cmd $command, $status)
    {
        return $status !== ApplicationCompletion::STATUS_NOT_STARTED || $section === $command->getSection();
    }
}
