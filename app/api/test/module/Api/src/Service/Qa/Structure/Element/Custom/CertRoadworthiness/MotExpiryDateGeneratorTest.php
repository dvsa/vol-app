<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDate;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDateFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDateGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * MotExpiryDateGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MotExpiryDateGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(mixed $isSelfservePageContainer, mixed $isNi, mixed $expectedEnableFileUploads): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getLicence->isNi')
            ->withNoArgs()
            ->andReturn($isNi);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);
        $elementGeneratorContext->shouldReceive('isSelfservePageContainer')
            ->withNoArgs()
            ->andReturn($isSelfservePageContainer);

        $dateWithThreshold = m::mock(DateWithThreshold::class);

        $dateWithThresholdGenerator = m::mock(DateWithThresholdGenerator::class);
        $dateWithThresholdGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext, 'P14M')
            ->once()
            ->andReturn($dateWithThreshold);

        $motExpiryDate = m::mock(MotExpiryDate::class);

        $motExpiryDateFactory = m::mock(MotExpiryDateFactory::class);
        $motExpiryDateFactory->shouldReceive('create')
            ->with($expectedEnableFileUploads, $dateWithThreshold)
            ->once()
            ->andReturn($motExpiryDate);

        $motExpiryDateGenerator = new MotExpiryDateGenerator($motExpiryDateFactory, $dateWithThresholdGenerator);

        $this->assertSame(
            $motExpiryDate,
            $motExpiryDateGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpTestGenerate(): array
    {
        return [
            [false, false, false],
            [false, true, false],
            [true, false, false],
            [true, true, true]
        ];
    }
}
