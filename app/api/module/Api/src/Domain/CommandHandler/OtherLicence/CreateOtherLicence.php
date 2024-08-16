<?php

/**
 * Create Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateOtherLicence as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Create Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateOtherLicence extends AbstractCommandHandler
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        $otherLicence = $this->createOtherLicenceObject($command);

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addId('otherLicence', $otherLicence->getId());
        $result->addMessage('Other licence created successfully');

        $applicationId = $otherLicence->getApplication()->getId();
        $data = [
            'id' => $applicationId,
            'section' => 'licenceHistory'
        ];
        $result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        return $result;
    }

    /**
     * @return OtherLicence
     */
    private function createOtherLicenceObject(Cmd $command)
    {
        $otherLicence = new OtherLicence();

        if ($command->getApplication() !== null) {
            $application = $this->getRepo()->getReference(Application::class, $command->getApplication());
            $otherLicence->setApplication($application);
        }

        if ($command->getPreviousLicenceType() !== null) {
            $previousLicenceType = $this->getRepo()->getRefdataReference($command->getPreviousLicenceType());
            $otherLicence->setPreviousLicenceType($previousLicenceType);
        }
        $otherLicence->updateOtherLicence(
            $command->getLicNo(),
            $command->getHolderName(),
            $command->getWillSurrender(),
            $command->getDisqualificationDate(),
            $command->getDisqualificationLength(),
            $command->getPurchaseDate()
        );

        return $otherLicence;
    }
}
