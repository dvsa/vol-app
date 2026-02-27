<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\CheckboxAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckboxAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CheckboxAnswerSaverTest extends MockeryTestCase
{
    private $fieldsetName;

    private $applicationStep;

    private $qaContext;

    private $genericAnswerWriter;

    private $genericAnswerFetcher;

    private $checkboxAnswerSaver;

    public function setUp(): void
    {
        $this->fieldsetName = 'fields123';

        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStep);

        $this->genericAnswerWriter = m::mock(GenericAnswerWriter::class);

        $this->genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);

        $this->checkboxAnswerSaver = new CheckboxAnswerSaver($this->genericAnswerWriter, $this->genericAnswerFetcher);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSaveChecked(): void
    {
        $postData = [
            $this->fieldsetName => [
                'qaElement' => '1'
            ]
        ];

        $this->genericAnswerWriter->shouldReceive('write')
            ->with($this->qaContext, true);

        $this->genericAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $postData)
            ->andReturn('4');

        $this->checkboxAnswerSaver->save($this->qaContext, $postData);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testSaveUnchecked(): void
    {
        $postData = [
            $this->fieldsetName => [
            ]
        ];

        $this->genericAnswerWriter->shouldReceive('write')
            ->with($this->qaContext, false);

        $this->genericAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $postData)
            ->andThrow(new NotFoundException());

        $this->checkboxAnswerSaver->save($this->qaContext, $postData);
    }
}
