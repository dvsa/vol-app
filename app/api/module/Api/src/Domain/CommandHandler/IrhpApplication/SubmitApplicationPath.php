<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProvider;
use Dvsa\Olcs\Api\Service\Qa\PostSubmit\IrhpApplicationPostSubmitHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationPath as SubmitApplicationPathCmd;
use Psr\Container\ContainerInterface;

/**
 * Submit application path
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SubmitApplicationPath extends AbstractCommandHandler
{
    /** @var QaContextFactory */
    private $qaContextFactory;

    /** @var SupplementedApplicationStepsProvider */
    private $supplementedApplicationStepsProvider;

    /** @var IrhpApplicationPostSubmitHandler */
    private $irhpApplicationPostSubmitHandler;

    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle command
     *
     * @param SubmitApplicationPathCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplication = $this->getRepo()->fetchUsingId($command);

        $supplementedApplicationSteps = $this->supplementedApplicationStepsProvider->get(
            $this->getRepo()->fetchUsingId($command)
        );

        foreach ($supplementedApplicationSteps as $supplementedApplicationStep) {
            $qaContext = $this->qaContextFactory->create(
                $supplementedApplicationStep->getApplicationStep(),
                $irhpApplication
            );

            if ($qaContext->isApplicationStepEnabled()) {
                $supplementedApplicationStep->getFormControlStrategy()->saveFormData(
                    $qaContext,
                    $command->getPostData()
                );
            }
        }

        $this->irhpApplicationPostSubmitHandler->handle($irhpApplication);

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->qaContextFactory = $container->get('QaContextFactory');
        $this->supplementedApplicationStepsProvider = $container->get(
            'QaSupplementedApplicationStepsProvider'
        );
        $this->irhpApplicationPostSubmitHandler = $container->get('QaIrhpApplicationPostSubmitHandler');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
