<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerSummaryProviderTest extends MockeryTestCase
{
    private $noOfPermitsAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->noOfPermitsAnswerSummaryProvider = new NoOfPermitsAnswerSummaryProvider();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'ecmt-no-of-permits',
            $this->noOfPermitsAnswerSummaryProvider->getTemplateName()
        );
    }

    public function testShouldIncludeSlug(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $this->assertTrue(
            $this->noOfPermitsAnswerSummaryProvider->shouldIncludeSlug($qaEntity)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $periodNameKey = 'period.name.key';
        $validityYear = 2025;
        $requiredEuro5 = 5;
        $requiredEuro6 = 7;

        $irhpPermitStockEntity = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStockEntity->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($periodNameKey);
        $irhpPermitStockEntity->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($validityYear);

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStockEntity);
        $irhpPermitApplicationEntity->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($requiredEuro5);
        $irhpPermitApplicationEntity->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($requiredEuro6);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplicationEntity);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplicationEntity);

        $element = m::mock(ElementInterface::class);

        $expectedTemplateVariables = [
            'validityYear' => $validityYear,
            'periodNameKey' => $periodNameKey,
            'emissionsCategories' => [
                [
                    'key' => 'qanda.common.no-of-permits.emissions-category.euro5',
                    'count' => $requiredEuro5
                ],
                [
                    'key' => 'qanda.common.no-of-permits.emissions-category.euro6',
                    'count' => $requiredEuro6
                ]
            ]
        ];

        $templateVariables = $this->noOfPermitsAnswerSummaryProvider->getTemplateVariables(
            $qaContext,
            $element,
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
