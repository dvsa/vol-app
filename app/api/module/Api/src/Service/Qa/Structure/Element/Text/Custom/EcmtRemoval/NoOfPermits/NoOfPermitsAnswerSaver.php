<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;

class NoOfPermitsAnswerSaver implements AnswerSaverInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerSaver
     */
    public function __construct(private GenericAnswerFetcher $genericAnswerFetcher, private AnswerWriter $answerWriter, private FeeCreator $feeCreator)
    {
    }

    /**
     * Write the number of permits required to persistent storage and cancel/create fees as appropriate
     *
     * @param QaContext $qaContext
     * @param array $postData
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $permitsRequired = $this->genericAnswerFetcher->fetch(
            $qaContext->getApplicationStepEntity(),
            $postData
        );

        $irhpApplicationEntity = $qaContext->getQaEntity();
        $this->answerWriter->write($irhpApplicationEntity, $permitsRequired);
        $this->feeCreator->create($irhpApplicationEntity, $permitsRequired);
    }
}
