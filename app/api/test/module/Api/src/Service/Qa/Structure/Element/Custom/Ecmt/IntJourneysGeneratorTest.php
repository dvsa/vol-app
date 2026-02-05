<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\IntJourneys;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\IntJourneysFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\IntJourneysGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IntJourneysGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IntJourneysGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTrueFalse')]
    public function testGenerate(mixed $isNi): void
    {
        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getLicence->isNi')
            ->andReturn($isNi);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->andReturn($irhpApplication);

        $radio = m::mock(Radio::class);

        $radioGenerator = m::mock(RadioGenerator::class);
        $radioGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext)
            ->once()
            ->andReturn($radio);

        $intJourneys = m::mock(IntJourneys::class);

        $intJourneysFactory = m::mock(IntJourneysFactory::class);
        $intJourneysFactory->shouldReceive('create')
            ->with($isNi, $radio)
            ->once()
            ->andReturn($intJourneys);

        $intJourneysGenerator = new IntJourneysGenerator($intJourneysFactory, $radioGenerator);

        $this->assertSame(
            $intJourneys,
            $intJourneysGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
