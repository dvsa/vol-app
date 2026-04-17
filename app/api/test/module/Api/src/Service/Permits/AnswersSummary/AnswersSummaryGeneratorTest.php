<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryGenerator;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowsAdderInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnswersSummaryGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswersSummaryGeneratorTest extends MockeryTestCase
{
    private $irhpApplication;

    private $answersSummary;

    private $answersSummaryFactory;

    private $headerAnswersSummaryRowsAdder;

    private $defaultAnswersSummaryRowsAdder;

    private $answersSummaryGenerator;

    private $customRowsAdderForType2;

    public function setUp(): void
    {
        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->answersSummary = m::mock(AnswersSummary::class);

        $this->answersSummaryFactory = m::mock(AnswersSummaryFactory::class);
        $this->answersSummaryFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($this->answersSummary);

        $this->headerAnswersSummaryRowsAdder = m::mock(AnswersSummaryRowsAdderInterface::class);

        $this->defaultAnswersSummaryRowsAdder = m::mock(AnswersSummaryRowsAdderInterface::class);

        $this->answersSummaryGenerator = new AnswersSummaryGenerator(
            $this->answersSummaryFactory,
            $this->headerAnswersSummaryRowsAdder,
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
        $this->irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(4);

        $this->headerAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpApplication, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $this->defaultAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpApplication, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $this->assertSame(
            $this->answersSummary,
            $this->answersSummaryGenerator->generate($this->irhpApplication, $isSnapshot)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGenerateWithCustomRowsAdder(mixed $isSnapshot): void
    {
        $this->irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(2);

        $this->headerAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpApplication, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $this->customRowsAdderForType2->shouldReceive('addRows')
            ->with($this->answersSummary, $this->irhpApplication, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $this->assertSame(
            $this->answersSummary,
            $this->answersSummaryGenerator->generate($this->irhpApplication, $isSnapshot)
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
