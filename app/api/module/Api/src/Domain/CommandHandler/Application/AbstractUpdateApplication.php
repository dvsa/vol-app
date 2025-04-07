<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

abstract class AbstractUpdateApplication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    protected array $sections = [];
    protected string $confirmMessage = '';

    public function handleCommand(CommandInterface $command)
    {
        $applicationId = $command->getId();

        /** @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchById(
            $applicationId,
            AbstractQuery::HYDRATE_OBJECT,
            $command->getVersion()
        );

        $this->updateApplication($application, $command);

        $this->getRepo()->save($application);

        foreach ($this->sections as $section) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateApplicationCompletionCmd::create(
                        ['id' => $applicationId, 'section' => $section]
                    )
                )
            );
        }

        $this->result->addId('Application', $applicationId);
        $this->result->addMessage($this->confirmMessage);
        return $this->result;
    }

    abstract protected function updateApplication(ApplicationEntity $application, CommandInterface $command): void;
}
