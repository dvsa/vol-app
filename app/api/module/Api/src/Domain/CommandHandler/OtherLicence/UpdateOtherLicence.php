<?php

/**
 * Update Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Update Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateOtherLicence extends AbstractCommandHandler
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $otherLicence = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $otherLicence->updateOtherLicence(
            $command->getLicNo(),
            $command->getHolderName(),
            $command->getWillSurrender(),
            $command->getDisqualificationDate(),
            $command->getDisqualificationLength(),
            $command->getPurchaseDate()
        );

        $this->getRepo()->save($otherLicence);
        $result->addMessage('Other licence record has been updated');
        $application = $otherLicence->getApplication();
        if ($application) {
            $data = [
                'id' => $application->getId(),
                'section' => 'licenceHistory'
            ];
            $result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));
        }
        return $result;
    }
}
