<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorListGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ElementGeneratorContextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ElementGeneratorContextGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $elementContainer = 'element-container';

        $applicationStep = m::mock(ApplicationStep::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $validatorList = m::mock(ValidatorList::class);

        $validatorListGenerator = m::mock(ValidatorListGenerator::class);
        $validatorListGenerator->shouldReceive('generate')
            ->with($applicationStep)
            ->once()
            ->andReturn($validatorList);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);

        $elementGeneratorContextFactory = m::mock(ElementGeneratorContextFactory::class);
        $elementGeneratorContextFactory->shouldReceive('create')
            ->with($validatorList, $qaContext, $elementContainer)
            ->once()
            ->andReturn($elementGeneratorContext);

        $elementGeneratorContextGenerator = new ElementGeneratorContextGenerator(
            $validatorListGenerator,
            $elementGeneratorContextFactory
        );

        $this->assertSame(
            $elementGeneratorContext,
            $elementGeneratorContextGenerator->generate($qaContext, $elementContainer)
        );
    }
}
