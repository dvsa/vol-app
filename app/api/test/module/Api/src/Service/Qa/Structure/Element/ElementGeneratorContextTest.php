<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\ElementContainer;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ElementGeneratorContextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ElementGeneratorContextTest extends MockeryTestCase
{
    private $validatorList;

    private $applicationStepEntity;

    private $qaEntity;

    private $qaContext;

    public function setUp(): void
    {
        $this->validatorList = m::mock(ValidatorList::class);

        $this->applicationStepEntity = m::mock(ApplicationStepEntity::class);

        $this->qaEntity = m::mock(QaEntityInterface::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStepEntity);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->qaEntity);
    }

    public function testGetValidatorList(): void
    {
        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            ElementContainer::SELFSERVE_PAGE
        );

        $this->assertSame(
            $this->validatorList,
            $elementGeneratorContext->getValidatorList()
        );
    }

    public function testGetApplicationStepEntity(): void
    {
        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            ElementContainer::SELFSERVE_PAGE
        );

        $this->assertSame(
            $this->applicationStepEntity,
            $elementGeneratorContext->getApplicationStepEntity()
        );
    }

    public function testGetQaEntity(): void
    {
        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            ElementContainer::SELFSERVE_PAGE
        );

        $this->assertSame(
            $this->qaEntity,
            $elementGeneratorContext->getQaEntity()
        );
    }

    public function testGetQaContext(): void
    {
        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            ElementContainer::SELFSERVE_PAGE
        );

        $this->assertSame(
            $this->qaContext,
            $elementGeneratorContext->getQaContext()
        );
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testGetAnswerValue(): void
    {
        $answerValue = 'foo';

        $this->qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            ElementContainer::SELFSERVE_PAGE
        );

        $elementGeneratorContext->getAnswerValue();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSelfservePageContainer')]
    public function testIsSelfservePageContainer(mixed $elementContainer, mixed $expected): void
    {
        $elementGeneratorContext = new ElementGeneratorContext(
            $this->validatorList,
            $this->qaContext,
            $elementContainer
        );

        $this->assertEquals(
            $expected,
            $elementGeneratorContext->isSelfservePageContainer()
        );
    }

    public static function dpIsSelfservePageContainer(): array
    {
        return [
            [ElementContainer::FORM_FRAGMENT, false],
            [ElementContainer::SELFSERVE_PAGE, true],
            [ElementContainer::ANSWERS_SUMMARY, false],
        ];
    }
}
