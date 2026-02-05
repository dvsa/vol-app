<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\SelfservePage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SelfservePageTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SelfservePageTest extends MockeryTestCase
{
    private $title;

    private $additionalViewData;

    private $applicationStep;

    private $questionText;

    private $submitOptionsName;

    private $nextStepSlug;

    private $selfservePage;

    public function setUp(): void
    {
        $this->title = 'title';

        $this->additionalViewData = [
            'property1' => 'value1',
            'property2' => 'value2',
        ];

        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->questionText = m::mock(QuestionText::class);

        $this->submitOptionsName = 'submit_options_name';

        $this->nextStepSlug = 'removals-cabotage';

        $this->selfservePage = new SelfservePage(
            $this->title,
            $this->additionalViewData,
            $this->applicationStep,
            $this->questionText,
            $this->submitOptionsName,
            $this->nextStepSlug
        );
    }

    public function testGenerate(): void
    {
        $applicationStepRepresentation = ['applicationStepRepresentation'];

        $questionTextRepresentation = ['questionTextRepresentation'];

        $this->applicationStep->shouldReceive('getRepresentation')
            ->andReturn($applicationStepRepresentation);

        $this->questionText->shouldReceive('getRepresentation')
            ->andReturn($questionTextRepresentation);

        $expectedRepresentation = [
            'title' => $this->title,
            'additionalViewData' => $this->additionalViewData,
            'applicationStep' => $applicationStepRepresentation,
            'questionText' => $questionTextRepresentation,
            'submitOptions' => $this->submitOptionsName,
            'nextStepSlug' => $this->nextStepSlug
        ];

        $this->assertSame(
            $expectedRepresentation,
            $this->selfservePage->getRepresentation()
        );
    }

    public function testGetQuestionText(): void
    {
        $this->assertSame(
            $this->questionText,
            $this->selfservePage->getQuestionText()
        );
    }
}
