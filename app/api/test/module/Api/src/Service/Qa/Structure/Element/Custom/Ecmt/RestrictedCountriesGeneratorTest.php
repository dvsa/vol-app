<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepository;
use Dvsa\Olcs\Api\Entity\Generic\Answer as AnswerEntity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\Common\PermitTypeConfig;
use Dvsa\Olcs\Api\Service\Permits\Common\StockBasedPermitTypeConfigProvider;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountries;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountry;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountryFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedCountriesGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $yesNoValue = 1;

        $irhpPermitStockId = 43;

        $restrictedCountryIds = ['GR', 'HU', 'IT', 'RU'];
        $restrictedCountriesQuestionKey = 'restricted.countries.question.key';

        $yesNo = m::mock(AnswerEntity::class);
        $yesNo->shouldReceive('getValue')
            ->andReturn($yesNoValue);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('hasCountryId')
            ->with('GR')
            ->andReturn(true);
        $irhpApplication->shouldReceive('hasCountryId')
            ->with('HU')
            ->andReturn(false);
        $irhpApplication->shouldReceive('hasCountryId')
            ->with('IT')
            ->andReturn(true);
        $irhpApplication->shouldReceive('hasCountryId')
            ->with('RU')
            ->andReturn(false);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getIrhpPermitWindow->getIrhpPermitStock->getId')
            ->andReturn($irhpPermitStockId);

        $applicationStep = m::mock(ApplicationStepEntity::class);

        $qaContext = m::mock(QaContext::class);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);
        $elementGeneratorContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);
        $elementGeneratorContext->shouldReceive('getQaContext')
            ->withNoArgs()
            ->andReturn($qaContext);

        $greeceRestrictedCountry = m::mock(RestrictedCountry::class);
        $hungaryRestrictedCountry = m::mock(RestrictedCountry::class);
        $italyRestrictedCountry = m::mock(RestrictedCountry::class);
        $russiaRestrictedCountry = m::mock(RestrictedCountry::class);

        $restrictedCountryFactory = m::mock(RestrictedCountryFactory::class);
        $restrictedCountryFactory->shouldReceive('create')
            ->with('GR', 'Greece', true)
            ->andReturn($greeceRestrictedCountry);
        $restrictedCountryFactory->shouldReceive('create')
            ->with('HU', 'Hungary', false)
            ->andReturn($hungaryRestrictedCountry);
        $restrictedCountryFactory->shouldReceive('create')
            ->with('IT', 'Italy', true)
            ->andReturn($italyRestrictedCountry);
        $restrictedCountryFactory->shouldReceive('create')
            ->with('RU', 'Russia', false)
            ->andReturn($russiaRestrictedCountry);

        $restrictedCountries = m::mock(RestrictedCountries::class);
        $restrictedCountries->shouldReceive('addRestrictedCountry')
            ->with($greeceRestrictedCountry)
            ->once()
            ->ordered();
        $restrictedCountries->shouldReceive('addRestrictedCountry')
            ->with($hungaryRestrictedCountry)
            ->once()
            ->ordered();
        $restrictedCountries->shouldReceive('addRestrictedCountry')
            ->with($italyRestrictedCountry)
            ->once()
            ->ordered();
        $restrictedCountries->shouldReceive('addRestrictedCountry')
            ->with($russiaRestrictedCountry)
            ->once()
            ->ordered();

        $restrictedCountriesFactory = m::mock(RestrictedCountriesFactory::class);
        $restrictedCountriesFactory->shouldReceive('create')
            ->with($yesNoValue, $restrictedCountriesQuestionKey)
            ->andReturn($restrictedCountries);

        $greeceCountry = m::mock(Country::class);
        $greeceCountry->shouldReceive('getCountryDesc')
            ->andReturn('Greece');
        $hungaryCountry = m::mock(Country::class);
        $hungaryCountry->shouldReceive('getCountryDesc')
            ->andReturn('Hungary');
        $italyCountry = m::mock(Country::class);
        $italyCountry->shouldReceive('getCountryDesc')
            ->andReturn('Italy');
        $russiaCountry = m::mock(Country::class);
        $russiaCountry->shouldReceive('getCountryDesc')
            ->andReturn('Russia');

        $countryRepo = m::mock(CountryRepository::class);
        $countryRepo->shouldReceive('fetchById')
            ->with('GR')
            ->andReturn($greeceCountry);
        $countryRepo->shouldReceive('fetchById')
            ->with('HU')
            ->andReturn($hungaryCountry);
        $countryRepo->shouldReceive('fetchById')
            ->with('IT')
            ->andReturn($italyCountry);
        $countryRepo->shouldReceive('fetchById')
            ->with('RU')
            ->andReturn($russiaCountry);

        $genericAnswerProvider = m::mock(GenericAnswerProvider::class);
        $genericAnswerProvider->shouldReceive('get')
            ->with($qaContext)
            ->andReturn($yesNo);

        $permitTypeConfig = m::mock(PermitTypeConfig::class);
        $permitTypeConfig->shouldReceive('getRestrictedCountriesQuestionKey')
            ->withNoArgs()
            ->andReturn($restrictedCountriesQuestionKey);
        $permitTypeConfig->shouldReceive('getRestrictedCountryIds')
            ->withNoArgs()
            ->andReturn($restrictedCountryIds);

        $stockBasedPermitTypeConfigProvider = m::mock(StockBasedPermitTypeConfigProvider::class);
        $stockBasedPermitTypeConfigProvider->shouldReceive('getPermitTypeConfig')
            ->with($irhpPermitStockId)
            ->andReturn($permitTypeConfig);

        $restrictedCountriesGenerator = new RestrictedCountriesGenerator(
            $restrictedCountriesFactory,
            $restrictedCountryFactory,
            $countryRepo,
            $genericAnswerProvider,
            $stockBasedPermitTypeConfigProvider
        );

        $this->assertSame(
            $restrictedCountries,
            $restrictedCountriesGenerator->generate($elementGeneratorContext)
        );
    }
}
