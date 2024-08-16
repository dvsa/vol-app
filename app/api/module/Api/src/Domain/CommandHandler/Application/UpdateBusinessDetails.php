<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Business Details
 * @NOTE   This handler calls the common version and adds the update application completion side effect
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateBusinessDetails extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @inheritdoc
     * @param \Dvsa\Olcs\Transfer\Command\Application\UpdateBusinessDetails $command
     */
    public function handleCommand(CommandInterface $command)
    {
        $data = $command->getArrayCopy();
        $data['id'] = $data['licence'];

        //  Update Business Details
        $updateResult = $this->handleSideEffect(
            DomainCmd\Licence\SaveBusinessDetails::create($data)
        );
        $this->result->merge($updateResult);

        //  Update Application Completion
        $this->result->merge(
            $this->handleSideEffect(
                DomainCmd\Application\UpdateApplicationCompletion::create(
                    [
                        'id' => $command->getId(),
                        'section' => 'businessDetails',
                        'data' => [
                            'hasChanged' => $updateResult->getFlag('hasChanged'),
                        ],
                    ]
                )
            )
        );

        return $this->result;
    }
}
