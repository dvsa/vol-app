<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class FixedAnswerQuestionHandler implements QuestionHandlerInterface
{
    /**
     * Create service instance
     *
     * @param string $answer
     * @return FixedAnswerQuestionHandler
     */
    public function __construct(private readonly GenericAnswerWriter $genericAnswerWriter, private $answer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QaContext $qaContext, array $requiredPermits)
    {
        $this->genericAnswerWriter->write($qaContext, $this->answer);
    }
}
