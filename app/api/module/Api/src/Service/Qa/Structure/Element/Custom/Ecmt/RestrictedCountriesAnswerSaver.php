<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedRestrictedCountryIdsProvider;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\Common\ArrayCollectionFactory;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class RestrictedCountriesAnswerSaver implements AnswerSaverInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return RestrictedCountriesAnswerSaver
     */
    public function __construct(private IrhpApplicationRepository $irhpApplicationRepo, private CountryRepository $countryRepo, private ArrayCollectionFactory $arrayCollectionFactory, private NamedAnswerFetcher $namedAnswerFetcher, private GenericAnswerWriter $genericAnswerWriter, private StockBasedRestrictedCountryIdsProvider $stockBasedRestrictedCountryIdsProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $applicationStepEntity = $qaContext->getApplicationStepEntity();
        $irhpApplicationEntity = $qaContext->getQaEntity();

        $restrictedCountries = $this->namedAnswerFetcher->fetch(
            $applicationStepEntity,
            $postData,
            'restrictedCountries'
        );

        $hasRestrictedCountries = ($restrictedCountries === 'Y');

        $countryReferences = $this->arrayCollectionFactory->create();

        if ($hasRestrictedCountries) {
            $selectedCountries = $this->namedAnswerFetcher->fetch(
                $applicationStepEntity,
                $postData,
                'yesContent'
            );

            $stockId = $irhpApplicationEntity->getFirstIrhpPermitApplication()
                ->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getId();

            $countryIds = $this->stockBasedRestrictedCountryIdsProvider->getIds($stockId);

            foreach ($countryIds as $countryId) {
                if (in_array($countryId, $selectedCountries)) {
                    $countryReferences->add(
                        $this->irhpApplicationRepo->getReference(Country::class, $countryId)
                    );
                }
            }
        }

        $this->genericAnswerWriter->write($qaContext, $hasRestrictedCountries, Question::QUESTION_TYPE_BOOLEAN);

        $irhpApplicationEntity->updateCountries($countryReferences);
        $this->irhpApplicationRepo->save($irhpApplicationEntity);
    }
}
