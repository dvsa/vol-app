<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom;

use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class EcmtRemovalNoOfPermitsGenerator implements QuestionTextGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return EcmtRemovalNoOfPermitsGenerator
     */
    public function __construct(private QuestionTextGenerator $questionTextGenerator, private FeeTypeRepository $feeTypeRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(QaContext $qaContext)
    {
        $feePerPermit = $qaContext->getQaEntity()->getFeePerPermit(
            null,
            $this->feeTypeRepo->getLatestByProductReference(FeeTypeEntity::FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF)
        );

        $questionText = $this->questionTextGenerator->generate($qaContext);
        $questionText->getGuidance()->getTranslateableText()->getParameter(0)->setValue($feePerPermit);

        return $questionText;
    }
}
