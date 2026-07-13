<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstance;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance\PrepareToSend;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PrepareToSend LetterInstance Test
 */
class PrepareToSendTest extends MockeryTestCase
{
    private function buildDescription(LetterInstance $letterInstance): string
    {
        $method = new \ReflectionMethod(PrepareToSend::class, 'buildDocumentDescription');
        $method->setAccessible(true);

        return $method->invoke(
            (new \ReflectionClass(PrepareToSend::class))->newInstanceWithoutConstructor(),
            $letterInstance
        );
    }

    private function mockChoice(string $label, string $inputType): LetterInstanceChoice
    {
        $letterChoice = m::mock(LetterChoice::class);
        $letterChoice->shouldReceive('getLabel')->andReturn($label);
        $letterChoice->shouldReceive('getInputType')->andReturn($inputType);

        $instanceChoice = m::mock(LetterInstanceChoice::class);
        $instanceChoice->shouldReceive('getLetterChoice')->andReturn($letterChoice);

        return $instanceChoice;
    }

    public function testDescriptionIncludesSelectedRadioChoiceLabels(): void
    {
        // VOL-7308: first/final is a letter *choice* within one letter type, so the
        // Docs & attachments description must carry the chosen variant.
        $letterType = m::mock(LetterType::class);
        $letterType->shouldReceive('getName')->andReturn('GV - Incomplete app New/Var');

        $letterInstance = m::mock(LetterInstance::class);
        $letterInstance->shouldReceive('getLetterType')->andReturn($letterType);
        $letterInstance->shouldReceive('getLetterInstanceChoices')->andReturn(new ArrayCollection([
            $this->mockChoice('First request', 'radio'),
            $this->mockChoice('Include urgent flag', 'checkbox'),
        ]));

        $this->assertSame(
            'GV - Incomplete app New/Var - First request',
            $this->buildDescription($letterInstance)
        );
    }

    public function testDescriptionIsJustTypeNameWithoutRadioChoices(): void
    {
        $letterType = m::mock(LetterType::class);
        $letterType->shouldReceive('getName')->andReturn('GV - Blank letter to operator');

        $letterInstance = m::mock(LetterInstance::class);
        $letterInstance->shouldReceive('getLetterType')->andReturn($letterType);
        $letterInstance->shouldReceive('getLetterInstanceChoices')->andReturn(new ArrayCollection());

        $this->assertSame('GV - Blank letter to operator', $this->buildDescription($letterInstance));
    }

    public function testDescriptionFallsBackWhenNoLetterType(): void
    {
        $letterInstance = m::mock(LetterInstance::class);
        $letterInstance->shouldReceive('getLetterType')->andReturn(null);
        $letterInstance->shouldReceive('getLetterInstanceChoices')->andReturn(new ArrayCollection());

        $this->assertSame('Letter', $this->buildDescription($letterInstance));
    }
}
