<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Common\ArrayCollectionFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerClearer;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerClearerInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class RestrictedCountriesAnswerClearer implements AnswerClearerInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     */
    public function __construct(private GenericAnswerClearer $genericAnswerClearer, private IrhpApplicationRepository $irhpApplicationRepo, private ArrayCollectionFactory $arrayCollectionFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(QaContext $qaContext)
    {
        $this->genericAnswerClearer->clear($qaContext);

        $irhpApplicationEntity = $qaContext->getQaEntity();

        $irhpApplicationEntity->updateCountries(
            $this->arrayCollectionFactory->create()
        );

        $this->irhpApplicationRepo->save($irhpApplicationEntity);
    }
}
