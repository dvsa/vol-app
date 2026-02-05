<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Psr\Container\ContainerInterface;

/**
 * Publish an application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Publish extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\Application\PublishValidationService
     */
    private $applicationValidationService;

    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\Variation\PublishValidationService
     */
    private $variationValidationService;

    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $validationService = ($application->getIsVariation()) ?
            $this->variationValidationService :
            $this->applicationValidationService;

        $errors = $validationService->validate($application);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $result = new Result();
        $result->merge($this->publishApplication($application));
        $result->merge($this->createTexTask($application));

        return $result;
    }

    /**
     * Publish the application
     *
     *
     * @return Result
     */
    protected function publishApplication(ApplicationEntity $application)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::create(
                [
                    'id' => $application->getId(),
                    'trafficArea' => $application->getTrafficArea()->getId(),
                ]
            )
        );
    }

    /**
     * Create any TEX tasks on the application
     *
     *
     * @return Result
     */
    protected function createTexTask(ApplicationEntity $application)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(
                [
                    'id' => $application->getId(),
                ]
            )
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->applicationValidationService = $container->get('ApplicationPublishValidationService');
        $this->variationValidationService = $container->get('VariationPublishValidationService');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
