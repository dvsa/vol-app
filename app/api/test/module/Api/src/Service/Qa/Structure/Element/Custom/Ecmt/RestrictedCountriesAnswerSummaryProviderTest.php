<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedCountriesAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesAnswerSummaryProviderTest extends MockeryTestCase
{
    private $irhpApplicationEntity;

    private $qaContext;

    private $element;

    private $restrictedCountriesAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);

        $this->qaContext = m::mock(QaContext::class);

        $this->element = m::mock(ElementInterface::class);

        $this->restrictedCountriesAnswerSummaryProvider = new RestrictedCountriesAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'ecmt-restricted-countries',
            $this->restrictedCountriesAnswerSummaryProvider->getTemplateName()
        );
    }

    public function testShouldIncludeSlug(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $this->assertTrue(
            $this->restrictedCountriesAnswerSummaryProvider->shouldIncludeSlug($qaEntity)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariablesWithCountries(mixed $isSnapshot): void
    {
        $hasRestrictedCountries = true;
        $country1Description = 'Spain';
        $country2Description = 'Hungary';

        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn($country1Description);

        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn($country2Description);

        $countries = [$country1, $country2];

        $this->irhpApplicationEntity->shouldReceive('getCountrys')
            ->andReturn($countries);

        $this->qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($hasRestrictedCountries);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpApplicationEntity);

        $expectedTemplateVariables = [
            'hasRestrictedCountries' => $hasRestrictedCountries,
            'countryNames' => [$country1Description, $country2Description]
        ];

        $templateVariables = $this->restrictedCountriesAnswerSummaryProvider->getTemplateVariables(
            $this->qaContext,
            $this->element,
            $isSnapshot
        );

        $this->assertEquals($expectedTemplateVariables, $templateVariables);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariablesWithoutCountries(mixed $isSnapshot): void
    {
        $hasRestrictedCountries = false;

        $this->qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($hasRestrictedCountries);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpApplicationEntity);

        $expectedTemplateVariables = [
            'hasRestrictedCountries' => $hasRestrictedCountries,
            'countryNames' => []
        ];

        $templateVariables = $this->restrictedCountriesAnswerSummaryProvider->getTemplateVariables(
            $this->qaContext,
            $this->element,
            $isSnapshot
        );

        $this->assertEquals($expectedTemplateVariables, $templateVariables);
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
