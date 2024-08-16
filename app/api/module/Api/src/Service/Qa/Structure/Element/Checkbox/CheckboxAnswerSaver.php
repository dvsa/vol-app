<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class CheckboxAnswerSaver implements AnswerSaverInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return CheckboxAnswerSaver
     */
    public function __construct(private GenericAnswerWriter $genericAnswerWriter, private GenericAnswerFetcher $genericAnswerFetcher)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $answerValue = true;
        try {
            $this->genericAnswerFetcher->fetch(
                $qaContext->getApplicationStepEntity(),
                $postData
            );
        } catch (NotFoundException) {
            $answerValue = false;
        }

        $this->genericAnswerWriter->write($qaContext, $answerValue);
    }
}
