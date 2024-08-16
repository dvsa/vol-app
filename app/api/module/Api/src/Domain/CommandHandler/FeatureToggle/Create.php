<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\FeatureToggle;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as ToggleEntity;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Create as CreateToggleCmd;

/**
 * Create a FeatureToggle
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'FeatureToggle';

    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var CreateToggleCmd $command
         */
        $toggle = ToggleEntity::create(
            $command->getConfigName(),
            $command->getFriendlyName(),
            $this->refData($command->getStatus())
        );

        $this->getRepo()->save($toggle);

        $this->result->addId('FeatureToggle', $toggle->getId());
        $this->result->addMessage("Feature toggle '{$toggle->getId()}' created");
        return $this->result;
    }
}
