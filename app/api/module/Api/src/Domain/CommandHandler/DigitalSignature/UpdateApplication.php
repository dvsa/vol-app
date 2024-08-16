<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication as UpdateApplicationCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class UpdateApplication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof UpdateApplicationCmd);

        $repo = $this->getRepo('Application');
        assert($repo instanceof ApplicationRepo);

        $applicationId = $command->getApplication();
        $application = $repo->fetchById($applicationId);

        assert($application instanceof ApplicationEntity);

        $digitalSignature = $repo->getReference(DigitalSignature::class, $command->getDigitalSignature());

        $application->updateDigitalSignature(
            $repo->getRefdataReference(RefData::SIG_DIGITAL_SIGNATURE),
            $digitalSignature
        );

        $repo->save($application);
        $this->result->addMessage('Digital signature added to Application ' . $applicationId);

        $this->result->merge(
            $this->handleSideEffect(
                UpdateCompletionCmd::create(
                    ['id' => $applicationId, 'section' => 'undertakings']
                )
            )
        );

        return $this->result;
    }
}
