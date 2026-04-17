<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowsAdderInterface;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\IpaAnswersSummaryGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IpaAnswersSummaryGeneratorTest
 */
class IpaAnswersSummaryGeneratorTest extends MockeryTestCase
{
    private $irhpPermitApplication;

    private $answersSummary;

    private $answersSummaryFactory;

    private $defaultAnswersSummaryRowsAdder;

    private $answersSummaryGenerator;

    private $customRowsAdderForType2;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->answersSummary = m::mock(AnswersSummary::class);

        $this->answersSummaryFactory = m::mock(AnswersSummaryFactory::class);
        $this->answersSummaryFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($this->answersSummary);

        $this->defaultAnswersSummaryRowsAdder = m::mock(AnswersSummaryRowsAdderInterface::class);

        $this->answersSummaryGenerator = new IpaAnswersSummaryGenerator(
            $this->answersSummaryFactory,
            $this->defaultAnswersSummaryRowsAdder
        );

        $this->customRowsAdderForType2 = m::mock(AnswersSummaryRowsAdderInterface::class);

        $this->answersSummaryGenerator->registerCustomRowsAdder(1, m::mock(AnswersSummaryRowsAdderInterface::class));
        $this->answersSummaryGenerator->registerCustomRowsAdder(2, $this->customRowsAdderForType2);
        $this->answersSummaryGenerator->registerCustomRowsAdder(3, m::mock(AnswersSummaryRowsAdderInterface::class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGenerateWithDefaultRowsAdder(mixed $isSnapshot): void
    {
        $this->irhpPermitApplication->shouldReceive('getIrhpApplication->getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(4);

        $this->defaultAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpPermitApplication, $isSnapshot)
            ->once();

        $this->assertSame(
            $this->answersSummary,
            $this->answersSummaryGenerator->generate($this->irhpPermitApplication, $isSnapshot)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGenerateWithCustomRowsAdder(mixed $isSnapshot): void
    {
        $this->irhpPermitApplication->shouldReceive('getIrhpApplication->getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(2);

        $this->defaultAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->never();

        $this->customRowsAdderForType2->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpPermitApplication, $isSnapshot)
            ->once();

        $this->assertSame(
            $this->answersSummary,
            $this->answersSummaryGenerator->generate($this->irhpPermitApplication, $isSnapshot)
        );
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
