<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * UpdateAuthSignature
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateAuthSignature extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $application->setAuthSignature($command->getAuthSignature() === 'Y');
        $this->getRepo()->save($application);

        $result = new \Dvsa\Olcs\Api\Domain\Command\Result();

        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create(
                    ['id' => $command->getId(), 'section' => 'declarationsInternal']
                )
            )
        );

        $result->addMessage('Auth signature updated');
        return $result;
    }
}
