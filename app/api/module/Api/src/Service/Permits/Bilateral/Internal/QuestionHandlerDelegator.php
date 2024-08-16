<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use RuntimeException;

class QuestionHandlerDelegator
{
    /** @var array */
    private $handlers = [];

    /**
     * Create service instance
     */
    public function __construct(private readonly QaContextFactory $qaContextFactory)
    {
    }

    /**
     * Get the appropriate instance of QuestionHandlerInterface associated with the specified application step
     *
     * @param array $requiredPermits
     *
     * @throws RuntimeException
     */
    public function delegate(
        IrhpPermitApplication $irhpPermitApplication,
        ApplicationStep $applicationStep,
        $requiredPermits
    ) {
        $questionId = $applicationStep->getQuestion()->getId();

        if (!isset($this->handlers[$questionId])) {
            throw new RuntimeException('No question handler specified for question id ' . $questionId);
        }

        $handler = $this->handlers[$questionId];
        $qaContext = $this->qaContextFactory->create($applicationStep, $irhpPermitApplication);
        $handler->handle($qaContext, $requiredPermits);
    }

    /**
     * Register a handler to be used for a given question id within an application path
     *
     * @param int $questionId
     */
    public function registerQuestionHandler($questionId, QuestionHandlerInterface $questionHandler)
    {
        $this->handlers[$questionId] = $questionHandler;
    }
}
