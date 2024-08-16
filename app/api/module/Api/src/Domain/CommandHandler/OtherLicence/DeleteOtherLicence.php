<?php

/**
 * Delete Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Delete Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class DeleteOtherLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $application = null;
        foreach ($command->getIds() as $otherLicence) {
            if (!$application) {
                $ol = $this->getRepo()->fetchById($otherLicence);
                $application = $ol->getApplication();
            }
            $this->getRepo()->delete(
                $this->getRepo()->fetchById($otherLicence)
            );

            $result->addId('otherLicence' . $otherLicence, $otherLicence);
            $result->addMessage('Other licence removed');
        }
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
