<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepository;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedPermitTypeConfigProvider;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class RestrictedCountriesGenerator implements ElementGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return RestrictedCountriesGenerator
     */
    public function __construct(private RestrictedCountriesFactory $restrictedCountriesFactory, private RestrictedCountryFactory $restrictedCountryFactory, private CountryRepository $countryRepo, private GenericAnswerProvider $genericAnswerProvider, private StockBasedPermitTypeConfigProvider $stockBasedPermitTypeConfigProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $irhpApplication = $context->getQaEntity();
        $qaContext = $context->getQaContext();

        $yesNo = null;
        try {
            $yesNo = $this->genericAnswerProvider->get($qaContext)->getValue();
        } catch (NotFoundException) {
        }

        $irhpPermitStockId = $irhpApplication->getFirstIrhpPermitApplication()
            ->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getId();

        $permitTypeConfig = $this->stockBasedPermitTypeConfigProvider->getPermitTypeConfig($irhpPermitStockId);

        $restrictedCountries = $this->restrictedCountriesFactory->create(
            $yesNo,
            $permitTypeConfig->getRestrictedCountriesQuestionKey()
        );

        $restrictedCountryIds = $permitTypeConfig->getRestrictedCountryIds();

        foreach ($restrictedCountryIds as $countryId) {
            $country = $this->countryRepo->fetchById($countryId);

            $restrictedCountry = $this->restrictedCountryFactory->create(
                $countryId,
                $country->getCountryDesc(),
                $irhpApplication->hasCountryId($countryId)
            );

            $restrictedCountries->addRestrictedCountry($restrictedCountry);
        }

        return $restrictedCountries;
    }
}
